"""Crée les archives sans fichiers de test locaux, identifiants ni outils tiers."""
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
from hashlib import sha256

root = Path(__file__).resolve().parents[1]
dist = root / 'dist'
dist.mkdir(exist_ok=True)
plugin = root / 'ubigi-plans-demo'
with ZipFile(dist / 'ubigi-plans-demo.zip', 'w', ZIP_DEFLATED) as archive:
 for path in sorted(plugin.rglob('*')):
  if path.is_file():
   archive.write(path, path.relative_to(root).as_posix())
included = ['ubigi-plans-demo', 'scripts', 'tests', 'docs', '.github', '.gitignore', 'README.md', 'LICENSE.txt', 'composer.json', 'composer.lock', 'phpcs.xml.dist']
with ZipFile(dist / 'ubigi-plans-demo-source.zip', 'w', ZIP_DEFLATED) as archive:
 for entry in included:
  item = root / entry
  for path in sorted(item.rglob('*')) if item.is_dir() else [item]:
   if path.is_file() and '__pycache__' not in path.parts:
    archive.write(path, path.relative_to(root).as_posix())
lines = []
for name in ['ubigi-plans-demo.zip', 'ubigi-plans-demo-source.zip']:
 digest = sha256((dist / name).read_bytes()).hexdigest()
 lines.append(f'{digest}  {name}')
 print(f'{name}: {(dist / name).stat().st_size} bytes · SHA256 {digest}')
(dist / 'SHA256SUMS.txt').write_text('\n'.join(lines) + '\n', encoding='ascii')
