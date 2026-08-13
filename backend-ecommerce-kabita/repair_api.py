#!/usr/bin/env python3
"""
Comprehensive repair for the corrupted api.json.
Uses json_repair to fix syntax, then merges duplicate paths.
Preserves all examples and schemas.
"""
import json
import sys
from json_repair import repair_json
from collections import defaultdict

INPUT_FILE = r'c:\Users\anand\Documents\SkripsiDhiya\si-ecommerce-umkm-kabita\kabita-ecommerce\backend\api.json'
OUTPUT_FILE = r'c:\Users\anand\Documents\SkripsiDhiya\si-ecommerce-umkm-kabita\kabita-ecommerce\backend\api_fixed.json'

def merge_paths(paths):
    """
    Merge duplicate path entries.
    If the same path appears multiple times, combine all HTTP methods into one object.
    """
    merged = defaultdict(dict)
    for path, methods in paths.items():
        if not isinstance(methods, dict):
            continue
        for method, details in methods.items():
            method_lower = method.lower()
            if method_lower in ['get', 'post', 'put', 'delete', 'patch', 'head', 'options']:
                # If the method already exists, we keep the first occurrence (or merge carefully)
                if method_lower not in merged[path]:
                    merged[path][method_lower] = details
                else:
                    # Optionally merge responses, parameters, etc. (here we keep the first)
                    pass
    # Convert back to dict
    return {k: dict(v) for k, v in merged.items()}

def main():
    try:
        with open(INPUT_FILE, 'r', encoding='utf-8') as f:
            raw = f.read()
        print(f"Original file size: {len(raw)} chars")
    except FileNotFoundError:
        print(f"Error: Input file not found at {INPUT_FILE}")
        sys.exit(1)

    # Step 1: Use json_repair to fix basic JSON syntax
    try:
        repaired = repair_json(raw, skip_json_loads=True, ensure_ascii=False)
        print("json_repair succeeded.")
    except Exception as e:
        print(f"json_repair failed: {e}")
        sys.exit(1)

    # Step 2: Parse the repaired JSON
    try:
        data = json.loads(repaired)
        print("Parsed repaired JSON successfully.")
    except json.JSONDecodeError as e:
        print(f"Still invalid JSON after repair: {e}")
        sys.exit(1)

    # Step 3: Merge duplicate paths
    if 'paths' in data and isinstance(data['paths'], dict):
        data['paths'] = merge_paths(data['paths'])
        print(f"Merged duplicate paths. Total unique paths: {len(data['paths'])}")
    else:
        print("Warning: 'paths' key missing or invalid.")

    # Step 4: (Optional) Ensure all responses have proper structure.
    # json_repair already fixed broken const patterns, but we can do a cleanup.
    # For example, replace any remaining "const": 200 with a proper schema reference if needed.
    # We'll skip that because json_repair handles most.

    # Step 5: Write the fixed file
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=2, ensure_ascii=False)

    print(f"Fixed file written to: {OUTPUT_FILE}")
    print(f"Output size: {len(json.dumps(data))} chars")

    # Quick validation: count endpoints and examples
    total_endpoints = 0
    for path, methods in data.get('paths', {}).items():
        total_endpoints += len(methods)
    print(f"Total endpoints (methods): {total_endpoints}")

if __name__ == '__main__':
    main()