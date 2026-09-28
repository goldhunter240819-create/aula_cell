import os
import glob
import re

files = glob.glob('apk/*.php')

for f in files:
    with open(f, 'r', encoding='utf-8') as file:
        content = file.read()
    
    # Update viewport meta tag
    content = re.sub(
        r'<meta name="viewport" content="(.*?)"',
        lambda m: f'<meta name="viewport" content="{m.group(1)}{", viewport-fit=cover" if "viewport-fit=cover" not in m.group(1) else ""}"',
        content
    )
    
    # Update pb-safe definition
    content = re.sub(
        r'\.pb-safe\s*\{\s*padding-bottom:\s*env\(safe-area-inset-bottom,\s*1rem\);\s*\}',
        '.pb-safe { padding-bottom: max(env(safe-area-inset-bottom), 1.5rem); }',
        content
    )
    
    with open(f, 'w', encoding='utf-8') as file:
        file.write(content)
    print(f"Updated {f}")
