#!/usr/bin/env python3
"""
Remove background from image using rembg library.
Usage: python remove_bg.py input.png output.png
Install: pip install rembg
"""
import sys
import os

def main():
    if len(sys.argv) < 3:
        print("Usage: remove_bg.py <input> <output>", file=sys.stderr)
        sys.exit(1)

    inp, out = sys.argv[1], sys.argv[2]
    if not os.path.exists(inp):
        print(f"Input not found: {inp}", file=sys.stderr)
        sys.exit(1)

    try:
        from rembg import remove
    except ImportError:
        print("rembg not installed. Run: pip install rembg", file=sys.stderr)
        sys.exit(2)

    try:
        with open(inp, 'rb') as f:
            data = f.read()
        result = remove(data)
        with open(out, 'wb') as f:
            f.write(result)
        print("OK")
    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == '__main__':
    main()
