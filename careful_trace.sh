#!/bin/bash

file="resources/views/layouts/app.blade.php"

echo "Careful line-by-line trace:"
depth=0
while IFS=: read -r line_num line_content; do
    # Count @if and @endif carefully
    if_adds=$(echo "$line_content" | grep -o '@if' | wc -l)
    endif_dels=$(echo "$line_content" | grep -o '@endif' | wc -l)
    
    depth=$((depth + if_adds - endif_dels))
    
    if [ "$if_adds" -gt 0 ] || [ "$endif_dels" -gt 0 ]; then
        printf "Line %3d (depth: %d): %s\n" "$line_num" "$depth" "$(echo "$line_content" | sed 's/^[[:space:]]*//')"
    fi
done < <(grep -n "@if\|@endif" "$file")

echo -e "\nFinal depth: $depth (0 = balanced)"
