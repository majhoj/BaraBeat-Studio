import assert from 'node:assert/strict';
import fs from 'node:fs';
import { createRequire } from 'node:module';
import path from 'node:path';
import vm from 'node:vm';
import { source, loadFunctions } from './helpers/music-context.mjs';

const editor = source('index.php');
const functionNames = ['normalizeSheetLineCount', 'getSheetPageCount', 'getSheetPageOffsetY',
  'base64ToUint8Array', 'createImagePagesPdf', 'getPdfScorePageBounds', 'exportCurrentSheetAsPdf'];
const shellContext = vm.createContext({});
loadFunctions(shellContext, source('app-shell.html'), functionNames);
const writer = vm.createContext({ Blob, TextEncoder, Uint8Array });
loadFunctions(writer, editor, ['createImagePagesPdf']);

function inspectPdf(bytes, count, images) {
  const text = bytes.toString('latin1');
  assert(text.startsWith('%PDF-1.4\n'));
  assert.equal((text.match(/\/Type \/Page\b/g) || []).length, count);
  assert(text.includes('/Count ' + count + ' >>'));
  const size = 3 + count * 3;
  const xrefOffset = Number(text.match(/startxref\n(\d+)\n%%EOF$/)[1]);
  const xref = text.slice(xrefOffset).split('\n');
  assert.equal(xref[0], 'xref');
  assert.equal(xref[1], '0 ' + size);
  assert(text.includes('/Size ' + size + ' /Root 1 0 R'));
  const objectText = id => {
    const start = Number(xref[id + 2].slice(0, 10));
    assert.equal(text.slice(start, start + (id + ' 0 obj\n').length), id + ' 0 obj\n');
    return text.slice(start, text.indexOf('\nendobj', start));
  };
  for (let id = 1; id < size; id++) objectText(id);
  const pageIds = Array.from({ length: count }, (_, i) => 3 + i * 3);
  const placements = [];
  assert(objectText(2).includes('/Kids [' + pageIds.map(id => id + ' 0 R').join(' ') + ']'));
  for (const [i, pageId] of pageIds.entries()) {
    const page = objectText(pageId);
    assert(page.includes('/MediaBox [0 0 595.28 841.89]'), 'Each sheet is A4 portrait');
    assert(page.includes('/Im0 ' + (pageId + 1) + ' 0 R'));
    assert(page.includes('/Contents ' + (pageId + 2) + ' 0 R'));
    for (const id of [pageId + 1, pageId + 2]) {
      const object = objectText(id);
      const streamStart = object.indexOf('\nstream\n') + 8;
      const length = Number(object.match(/\/Length (\d+)/)[1]);
      assert.equal(object.slice(streamStart + length), '\nendstream', 'Exact binary stream length');
      if (images && id === pageId + 1) {
        assert.deepEqual(Buffer.from(object.slice(streamStart, streamStart + length), 'latin1'),
          Buffer.from(images[i].jpegBytes), 'Images retain their order and bytes');
      }
    }
    const matrix = objectText(pageId + 2).match(/([\d.-]+) 0 0 ([\d.-]+) ([\d.-]+) ([\d.-]+) cm/);
    placements.push(matrix.slice(1).map(Number));
  }
  return placements;
}

for (const count of [1, 2, 3, 20]) {
  const images = Array.from({ length: count }, (_, i) => ({
    width: 2480, height: 3508, jpegBytes: Uint8Array.of(255, 216, i, 128, 0, 255, 217)
  }));
  const blob = writer.createImagePagesPdf(images);
  assert.equal(blob.type, 'application/pdf');
  inspectPdf(Buffer.from(await blob.arrayBuffer()), count, images);
}

for (const edgeCase of [false, true]) {
  const images = Array.from({ length: 3 }, (_, i) => ({
    width: 2480, height: 3508, jpegBytes: Uint8Array.of(255, 216, i, 255, 217),
    contentBounds: edgeCase && i === 2
      ? { x: 0, y: 0, width: 2480, height: 3508 }
      : { x: 200, y: 200, width: 2080, height: 3070 }
  }));
  const placements = inspectPdf(Buffer.from(await writer.createImagePagesPdf(images).arrayBuffer()), 3, images);
  const baseWidth = 595.28 - 44;
  for (const [i, [width, height, x, y]] of placements.entries()) {
    const scale = width / images[i].width;
    const bounds = images[i].contentBounds;
    assert.equal(width, placements[0][0], 'Consistent scale on every page');
    if (!edgeCase) assert(Math.abs(width / baseWidth / 1.2 - 0.933) < 0.00001, '93.3% of the enlarged version');
    else assert(width / baseWidth < 1.2 * 0.933, 'Reduce zoom only if edge annotations would be clipped');
    const left = x + bounds.x * scale;
    const right = left + bounds.width * scale;
    const bottom = y + height - (bounds.y + bounds.height) * scale;
    const top = bottom + bounds.height * scale;
    assert(right <= 595.28 - 9.98);
    assert(bottom >= 9.98 && top <= 841.89 - 9.98, 'Notes, legend and page number stay inside A4');
    assert(Math.abs(left - 19 * 72 / 25.4) < 0.02, '19 mm left margin');
    assert(Math.abs(841.89 - top - 20 * 72 / 25.4) < 0.02, '20 mm top margin');
  }
}

async function checkExport(lineCount, failAt = -1, title = 'Test score') {
  const pages = Math.max(1, Math.ceil(lineCount / 10));
  const original = { width: '1086', height: '1516', viewBox: '0 0 1086 1516' };
  const attributes = { ...original };
  const urls = new Map();
  const revocations = [];
  const renders = [];
  const downloads = [];
  const alerts = [];
  let canvasCount = 0;
  let clears = 0;
  let serial = 0;
  let loads = 0;
  let removedFrames = 0;
  const scoreElements = [
    { id: 'basis', frame: true, box: { x: 0, y: 0, width: 1050, height: 1480 } },
    ...Array.from({ length: pages }, (_, i) => ({ id: 'basis', box: {
      x: i === 0 ? 65 : 70, y: i * 1550 + (i === 0 ? 50 : 130),
      width: i === 0 ? 950 : 945, height: i === 0 ? 1398 : 1318
    } })),
    { id: 'timeline_metadata', box: { x: -9999, y: -9999, width: 99999, height: 99999 } }
  ];
  if (lineCount > 10) scoreElements.push({ id: 'tone', box: { x: 100, y: 1730, width: 20, height: 20 } });
  const originalElements = JSON.stringify(scoreElements);
  const footers = Array.from({ length: pages }, (_, i) => ({
    textContent: `${i + 1}/${pages}`,
    style: { fontSize: '11px', fontFamily: 'sans-serif' },
    attributes: { 'font-size': '11', 'font-family': 'sans-serif', fill: '#666', 'text-anchor': 'end', x: '1016' },
    getAttribute(name) { return this.attributes[name]; },
    setAttribute(name, value) { this.attributes[name] = String(value); }
  }));
  const context = vm.createContext({
    Blob, TextEncoder, Uint8Array, atob,
    zeilenAnzahl: lineCount, zeilenProBlatt: 10,
    sheetWidth: 1050, sheetPageHeight: 1480, sheetPageGapY: 70,
    titel: { attr: () => title }, uiText: key => key,
    sanitizeDownloadFileName: name => name,
    buildExportSvgContent: () => '<svg>original export</svg>',
    exportableElementSelector: 'exportable',
    s: { selectAll: () => scoreElements.map(element => ({
      attr: () => element.id,
      hasClass: name => name === 'sheet-page-background' && element.frame === true,
      getBBox: () => element.box
    })) },
    DOMParser: class {
      parseFromString(text, type) {
        assert.equal(text, '<svg>original export</svg>');
        assert.equal(type, 'image/svg+xml');
        return { documentElement: {
          setAttribute: (name, value) => { attributes[name] = String(value); },
          getAttribute: name => attributes[name],
          querySelectorAll: selector => selector === '.sheet-page-number'
            ? footers : [{ remove() { removedFrames++; } }]
        } };
      }
    },
    XMLSerializer: class { serializeToString() { return JSON.stringify(attributes); } },
    URL: {
      createObjectURL(blob) { const key = 'blob:' + ++serial; urls.set(key, blob); return key; },
      revokeObjectURL(key) { revocations.push(key); urls.delete(key); }
    },
    async loadImageFromObjectUrl(url) {
      if (loads++ === failAt) throw new Error('Render failed');
      const data = JSON.parse(await urls.get(url).text());
      renders.push(data);
      return { naturalWidth: Number(data.width), naturalHeight: Number(data.height) };
    },
    document: {
      body: { appendChild() {} },
      createElement(tag) {
        if (tag === 'canvas') {
          canvasCount++;
          return { width: 0, height: 0,
            getContext: () => ({ fillRect() { clears++; }, drawImage() {}, measureText: text => ({ width: text.length * 6 }) }),
            toDataURL: () => 'data:image/jpeg;base64,/9j/2Q==' };
        }
        assert.equal(tag, 'a');
        return { click() { downloads.push(urls.get(this.href)); }, remove() {} };
      }
    },
    window: { setTimeout: callback => callback() },
    console: { error() {} }, alert: message => alerts.push(message)
  });
  loadFunctions(context, editor, functionNames);
  for (const name of functionNames) {
    assert.equal(context[name].toString(), shellContext[name].toString(), name + ': offline parity');
  }
  await context.exportCurrentSheetAsPdf();
  assert.equal(removedFrames, 1, 'Remove the decorative frame only from the PDF copy');
  assert.equal(JSON.stringify(scoreElements), originalElements, 'Original geometry stays unchanged');
  assert.deepEqual(JSON.parse(JSON.stringify(context.getPdfScorePageBounds(pages))),
    Array.from({ length: pages }, (_, i) => i === 0
      ? { x: 63, y: 48, width: 954, height: 1402 }
      : { x: 68, y: 48, width: 949, height: 1402 }),
    'Pages without a title retain the same vertical origin as the first page');
  footers.forEach((footer, i) => {
    assert.equal(footer.textContent, `${title} - ${i + 1}/${pages}`, 'Full current title precedes page numbers exactly once');
    assert.equal(footer.attributes['font-size'], '11');
    assert.equal(footer.attributes['font-family'], 'sans-serif');
    assert.equal(footer.attributes.fill, '#666');
    assert.equal(footer.attributes['text-anchor'], 'end');
    if (title.length > 200) {
      assert(Number(footer.attributes.textLength) <= 953, 'Long names remain within the score width');
      assert.equal(footer.attributes.lengthAdjust, 'spacingAndGlyphs');
    } else assert.equal(footer.attributes.textLength, undefined, 'Normal footer typography stays unchanged');
  });
  assert.equal(canvasCount, 1, 'Reuse a single canvas for all pages');
  assert.equal(urls.size, 0, 'Release page and PDF URLs, including failed renders');
  assert.equal(new Set(revocations).size, revocations.length);
  assert.deepEqual(original, { width: '1086', height: '1516', viewBox: '0 0 1086 1516' });
  if (failAt >= 0) {
    assert.equal(downloads.length, 0, 'Never download an incomplete PDF');
    assert.equal(alerts.length, 1);
    return;
  }
  assert.equal(renders.length, pages);
  assert.equal(clears, pages, 'No previous sheet remains on the reused canvas');
  if (pages === 1) {
    assert.deepEqual(renders[0], original, 'Single-page export keeps its original SVG viewport');
  } else {
    renders.forEach((render, i) => assert.deepEqual(render, {
      width: '1050', height: '1480', viewBox: `0 ${i * 1550} 1050 1480`
    }, 'Use sheet boundaries, not overall content bounds; skip the 70-unit gaps'));
  }
  assert.equal(downloads.length, 1, 'All sheets go into one PDF download');
  const placements = inspectPdf(Buffer.from(await downloads[0].arrayBuffer()), pages);
  for (const [width, height, , y] of placements) {
    assert.equal(width, placements[0][0], 'Same staff scale on every sheet');
    assert.equal(height, placements[0][1]);
    assert.equal(y, placements[0][3], 'Corresponding staff rows and footers align vertically');
  }
  assert.equal(alerts.length, 0);
}
for (const lines of [10, 20, 21, 30, 40, 80]) await checkExport(lines);
await checkExport(30, 1);
await checkExport(20, -1, 'Rhythm & <Solo> \u00e4\u00e9');
await checkExport(20, -1, 'Long rhythm name '.repeat(30));
console.log('PDF export: 93.3% scale, 19 mm left / 20 mm top margins, aligned staff rows, named footers, A4 pages, edge protection, immutable score, cleanup and offline parity checked.');

if (process.argv.includes('--browser')) {
  const { chromium, webkit } = createRequire(import.meta.url)(process.env.BARABEAT_PLAYWRIGHT_MODULE || 'playwright');
  const browserType = process.env.BARABEAT_TEST_BROWSER === 'webkit' ? webkit : chromium;
  const browser = await browserType.launch({ headless: true });
  try {
    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('dialog', dialog => dialog.type() === 'beforeunload' ? dialog.accept() : dialog.dismiss());
    const base = process.env.BARABEAT_TEST_URL || 'http://127.0.0.1:8877/';
    await page.goto(new URL('app-shell.html', base).href);
    await page.waitForFunction(() => typeof loadRhythmContent === 'function' && typeof s !== 'undefined');
    async function exportAndCheck(name, expectedPages) {
      const before = await page.evaluate(() => buildSerializedRhythm());
      const downloading = page.waitForEvent('download');
      const exportedFooters = await page.evaluate(async () => {
        const originalLoad = loadImageFromObjectUrl;
        const snapshots = [];
        loadImageFromObjectUrl = async function (url) {
          const svg = new DOMParser().parseFromString(await (await fetch(url)).text(), 'image/svg+xml');
          snapshots.push(Array.from(svg.querySelectorAll('.sheet-page-number'), node => ({
            label: node.textContent, fontSize: node.style.fontSize || node.getAttribute('font-size'),
            fontFamily: node.style.fontFamily || node.getAttribute('font-family'), fill: node.getAttribute('fill'),
            anchor: node.style.textAnchor || node.getAttribute('text-anchor')
          })));
          return originalLoad(url);
        };
        try { await exportCurrentSheetAsPdf(); }
        finally { loadImageFromObjectUrl = originalLoad; }
        return { title: titel.attr('text'), snapshots };
      });
      assert.equal(exportedFooters.snapshots.length, expectedPages);
      for (const snapshot of exportedFooters.snapshots) {
        assert.deepEqual(snapshot, Array.from({ length: expectedPages }, (_, i) => ({
          label: `${exportedFooters.title} - ${i + 1}/${expectedPages}`,
          fontSize: '11px', fontFamily: 'sans-serif', fill: '#666666', anchor: 'end'
        })));
      }
      const download = await downloading;
      assert.equal(await download.failure(), null);
      const bytes = fs.readFileSync(await download.path());
      const placements = inspectPdf(bytes, expectedPages);
      for (const [width, height, , y] of placements) {
        assert.equal(width, placements[0][0], 'Same scale, including empty final sheets');
        assert.equal(height, placements[0][1]);
        assert.equal(y, placements[0][3], 'All sheets use the same vertical placement');
      }
      assert.equal(await page.evaluate(() => buildSerializedRhythm()), before, 'Score data unchanged');
      if (process.env.BARABEAT_PDF_TEST_DIR) {
        fs.mkdirSync(process.env.BARABEAT_PDF_TEST_DIR, { recursive: true });
        await download.saveAs(path.join(process.env.BARABEAT_PDF_TEST_DIR, name + '.pdf'));
      }
      console.log(`${browserType.name()}: ${name}: ${expectedPages} pages, ${bytes.length} bytes`);
    }
    for (const [name, count] of [['Tiriba', 1], ['Kuku Solo', 2], ['Djaa Djembe, Sangban, Dundunba', 4]]) {
      const score = source('Noten/' + name + '.bbs');
      await page.evaluate(({ name, score }) => loadRhythmContent(name, score, null, { remember: false }), { name, score });
      assert.equal(await page.evaluate(() => getSheetPageCount(zeilenAnzahl)), count);
      await exportAndCheck(name, count);
    }
    await page.evaluate(() => addSheetPage());
    await exportAndCheck('Djaa with empty final sheet', 5);
    // Export the same current score with no network, including all SVG rasterization.
    await page.context().setOffline(true);
    await exportAndCheck('Djaa offline', 5);
    assert.deepEqual(errors, []);
  } finally {
    await browser.close();
  }
}
