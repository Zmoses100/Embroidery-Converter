#!/bin/bash

echo "CHECKING @section DETAILS (should be 1-to-1):"
echo "============================================="

# Check a few files to verify
for file in resources/views/auth/login.blade.php resources/views/convert/show.blade.php resources/views/layouts/app.blade.php; do
    echo -e "\n$file:"
    echo "  @section lines:"
    grep -n "@section" "$file" | head -5
    echo "  @endsection lines:"
    grep -n "@endsection" "$file" | head -5
done

echo -e "\n\nCHECKING FOR @include/@component IN ALL FILES:"
echo "================================================"
grep -r "@include\|@component" resources/views/ --include="*.blade.php" | head -20

echo -e "\n\nCHECKING FOR MALFORMED DIRECTIVES:"
echo "===================================="
# Look for common mistakes: missing @ symbol, incorrect spelling, unmatched braces
grep -rn "^[[:space:]]*@[a-z]*([^)]*\$\|^[[:space:]]*@[a-z]*[^(][^)]" resources/views/ --include="*.blade.php" | grep -v "@[a-z]*(" | grep -v "@[a-z]*\s" | head -20

