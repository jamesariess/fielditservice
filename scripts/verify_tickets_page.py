import sys, re

html = sys.stdin.read()

ac = len(re.findall(r'class="action-card', html))
rc = len(re.findall(r'class="report-card', html))
tc = len(re.findall(r'class="timeout-card', html))
print(f'Action cards: {ac}, Report cards: {rc}, Timeout cards: {tc}')

reports = re.findall(r'id="report-(\d+)"', html)
print(f'Report divs: {len(reports)} -> {reports}')

tinputs = re.findall(r'id="(to-\d+-[^"]+)"', html)
print(f'Timeout inputs: {len(tinputs)}')
for t in tinputs[:8]:
    print(f'  {t}')

# Check modal
modal = 'id="new-ticket-modal"' in html
print(f'New ticket modal: {modal}')

# Check map
mp = 'id="tt-map"' in html
print(f'Map element: {mp}')

# Check equipment search
es = 'id="tt-manufacturer-search"' in html
print(f'Equipment search: {es}')

# Check report card content (should be empty before JS)
for m in re.finditer(r'<div id="report-(\d+)"[^>]*>(.*?)</div>', html, re.DOTALL):
    tid = m.group(1)
    text = re.sub(r'<[^>]+>', '', m.group(2)).strip()
    print(f'Report {tid} content (before JS): {repr(text[:80])}')
