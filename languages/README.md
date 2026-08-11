# BaraBeat translations

Only visible labels and messages are translated. Persisted values, logical
identifiers, file-format fields and audio identifiers must never be translated.

Known protected values include:

- instrument identifiers: `Djembe_1`, `Djembe_2`, `Djembe_3`, `Dreierbass`,
  `Kenkeni`, `Sangban`, `Doundoun`
- rhythm identifiers: `binaer`, `tenaer`, `neunaer`
- tuplet identifiers: `triplet`, `quartuplet`
- pattern and function values: `Call`, `Intro`, `Outro`, `Echauffement`,
  `Begleitung`, `Solo`
- hand and playback values such as `H2H`, `HOH` and sample names
- edition feature IDs such as `wavExport`, `advancedMixer`, `maxPages` and
  `maxArrangementBlocks`

Some of these values are currently also used as visible labels. Until display
labels and stored values have been separated explicitly, translate them through
a display mapping only. Never replace the underlying value in `.bbs` content,
IndexedDB records, timeline metadata or player data.

German is the reference catalog. English is the fallback catalog. Every new
key must be added to both catalogs before it is used. French, Spanish and
Portuguese catalogs can be added later without changing the API.
