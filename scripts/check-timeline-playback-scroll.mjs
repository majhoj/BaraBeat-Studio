import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../JS/timeline.js', import.meta.url), 'utf8');
const start = source.indexOf('function scrollTimelinePlaybackBarIntoView(');
const end = source.indexOf('\nfunction ', start + 1);
assert(start >= 0 && end > start, 'Timeline scroll function missing');

function createLayout({ trackWidth = 600, columnWidth = 600, scale = 1, leadBars = 0 } = {}) {
  const panel = makeElement(null, 0, 1000, 1000, 'auto');
  const column = makeElement(panel, 200, columnWidth, Math.max(columnWidth, trackWidth), 'auto');
  const viewport = makeElement(column, 0, trackWidth, trackWidth, 'visible');
  const track = makeElement(viewport, 0, trackWidth, 112 + (120 + leadBars) * 108 + 42, 'auto');
  const label = {
    getBoundingClientRect() {
      const left = track.getBoundingClientRect().left;
      return { left, right: left + 112 * scale, width: 112 * scale };
    }
  };
  const playhead = {
    getBoundingClientRect() {
      const left = label.getBoundingClientRect().right + leadBars * 108 * scale;
      return { left, right: left + scale, width: leadBars ? scale : 0 };
    }
  };
  viewport.querySelector = () => playhead;
  let currentBar = 1;
  let hidden = false;
  const bar = {
    closest() { return track; },
    getBoundingClientRect() {
      if (hidden) return { left: 0, right: 0, width: 0 };
      const left = track.getBoundingClientRect().left +
        (112 + (currentBar - 1 + leadBars) * 108 - track.scrollLeft) * scale;
      return { left, right: left + 108 * scale, width: 108 * scale };
    }
  };
  track.closest = selector => selector === '#timelinePanel' ? panel : viewport;
  track.querySelector = () => label;

  function makeElement(parentElement, offset, width, scrollWidth, overflowX) {
    const classes = new Set();
    return {
      parentElement, clientWidth: width, offsetWidth: width, clientLeft: 0,
      get scrollWidth() {
        return classes.has('is-following-playback')
          ? Math.max(scrollWidth, 120 * 108 + width)
          : scrollWidth;
      },
      classList: {
        add(value) { classes.add(value); },
        remove(value) { classes.delete(value); }
      },
      scrollLeft: 0, scrollTop: 73, overflowX,
      contains(element) {
        for (let node = element; node; node = node.parentElement) {
          if (node === this) return true;
        }
        return false;
      },
      getBoundingClientRect() {
        const left = parentElement
          ? parentElement.getBoundingClientRect().left + (offset - parentElement.scrollLeft) * scale
          : offset;
        return { left, right: left + width * scale, width: width * scale };
      },
      scrollTo() {
        // Simulate a smooth scroll that has been deferred or cancelled by the browser.
      }
    };
  }

  const context = vm.createContext({
    window: { getComputedStyle: element => ({ overflowX: element.overflowX }) },
    document: { documentElement: { clientWidth: 1000 * scale } },
    bar
  });
  vm.runInContext(source.slice(start, end), context);
  return {
    panel, column, track, bar, label,
    follow(number, progress = 0) {
      currentBar = number;
      context.progress = progress;
      const geometry = vm.runInContext('scrollTimelinePlaybackBarIntoView(bar, progress)', context);
      for (const element of [panel, column, track]) {
        assert.equal(element.scrollTop, 73, 'Playback must not move the view vertically');
      }
      return geometry;
    },
    hide() { hidden = true; },
    assertVisible() {
      const rect = bar.getBoundingClientRect();
      const viewportLeft = Math.max(column.getBoundingClientRect().left, label.getBoundingClientRect().right);
      const viewportRight = Math.min(column.getBoundingClientRect().right, track.getBoundingClientRect().right);
      assert(rect.left >= viewportLeft - 1 && rect.right <= viewportRight + 1,
        `Bar ${currentBar} is clipped: ${JSON.stringify({ rect, viewportLeft, viewportRight })}`);
    },
    assertAnchored() {
      this.assertVisible();
      const expectedLeft = Math.max(column.getBoundingClientRect().left, label.getBoundingClientRect().right) +
        leadBars * 108 * scale;
      assert(Math.abs(bar.getBoundingClientRect().left - expectedLeft) < 1,
        `Bar ${currentBar} must start at the playback anchor`);
    }
  };
}

const normal = createLayout();
normal.follow(1);
assert.equal(normal.track.scrollLeft, 0, 'The first bar must remain at the beginning');
normal.assertAnchored();
for (let number = 2; number <= 120; number++) {
  const previousLeft = normal.track.scrollLeft;
  normal.follow(number);
  normal.assertAnchored();
  assert.equal(normal.track.scrollLeft - previousLeft, 108,
    'Every bar change must advance the view by exactly one bar, including the final bar');
}
normal.follow(10);
assert(normal.track.scrollLeft > 0, 'An offscreen bar must move the track even without smooth-scroll support');
normal.assertAnchored();
assert.equal(normal.column.scrollLeft, 0, 'The column must remain stationary when the track can scroll');
normal.follow(99);
normal.assertAnchored();
normal.follow(98);
normal.assertAnchored();
normal.follow(1);
normal.assertAnchored();
assert.equal(normal.track.scrollLeft, 0, 'Restarting must reveal the beginning again');
normal.follow(120);
normal.assertAnchored();
assert(normal.track.scrollLeft <= normal.track.scrollWidth - normal.track.clientWidth);

// An expanded track is clipped by its outer column; the playback tail permits alignment.
const outerScroll = createLayout({ trackWidth: 112 + 120 * 108 + 42 });
outerScroll.follow(99);
outerScroll.assertAnchored();
assert.equal(outerScroll.column.scrollLeft, 0, 'Following must keep the instrument column in place');

// A bar can be inside the track bounds and still be clipped by its parent.
const clipped = createLayout({ trackWidth: 1400 });
clipped.follow(8);
clipped.assertAnchored();

const scaled = createLayout({ scale: 0.75 });
scaled.follow(99);
scaled.assertAnchored();
scaled.follow(1);
scaled.assertAnchored();

const hidden = createLayout();
hidden.hide();
hidden.follow(99);
assert.equal(hidden.track.scrollLeft, 0, 'A hidden timeline must not scroll');

const desktop = createLayout({ leadBars: 2 });
const desktopScaled = createLayout({ leadBars: 2, scale: 0.75 });
for (const layout of [desktop, desktopScaled]) {
  for (const number of [1, 2, 99, 120, 1]) {
    layout.follow(number);
    layout.assertAnchored();
    assert.equal(layout.track.scrollLeft, (number - 1) * 108, 'Visual lead-in must not add musical bars');
  }
}

for (const layout of [normal, clipped, scaled, outerScroll, desktop, desktopScaled]) {
  for (const barNumber of [1, 99, 120]) {
    for (const progress of [0, 0.25, 0.5, 0.75, 1]) {
      const geometry = layout.follow(barNumber, progress);
      assert.equal(geometry.barWidth, 108, 'Animation uses unscaled CSS pixel widths');
      assert.equal(layout.track.scrollLeft, (barNumber - 1 + progress) * 108,
        'Scroll must cover the entire current bar, including the final bar');
    }
  }
}

console.log('Timeline playback scroll: complete bar motion, final bar, restart, late start and scaling checked.');
