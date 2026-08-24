import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const rootDir = process.cwd();
const indexSource = fs.readFileSync(path.join(rootDir, 'index.php'), 'utf8');

const cycleFunctionMatch = indexSource.match(
  /cycleRepeatCount\s*=\s*function\s*\(\)\s*\{[\s\S]*?\n\};/
);

if (!cycleFunctionMatch) {
  throw new Error('cycleRepeatCount wurde in index.php nicht gefunden.');
}

const cycleFunctionSource = cycleFunctionMatch[0];

if (!/if\s*\(zahl\s*>\s*8\)/.test(cycleFunctionSource)) {
  throw new Error('Das Wiederholungszeichen ist nicht auf maximal 8 Wiederholungen begrenzt.');
}

if (/if\s*\(zahl\s*>\s*4\)/.test(cycleFunctionSource)) {
  throw new Error('Die alte Obergrenze von 4 Wiederholungen ist noch aktiv.');
}

console.log('Wiederholungszeichen: Obergrenze 8 ist aktiv.');
