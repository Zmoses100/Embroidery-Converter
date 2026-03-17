#!/bin/bash

RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

echo "Checking ALL blade files for unbalanced @if/@endif..."
echo "======================================================"

find resources/views -name "*.blade.php" -type f | sort | while read file; do
    depth=0
    balanced=1
    
    while IFS=: read -r line_num line_content; do
        if_adds=$(echo "$line_content" | grep -o '@if' | wc -l)
        endif_dels=$(echo "$line_content" | grep -o '@endif' | wc -l)
        
        depth=$((depth + if_adds - endif_dels))
        
        if [ "$depth" -lt 0 ]; then
            echo -e "${RED}❌ $file${NC}"
            echo "   Line $line_num: More @endif than @if (depth goes negative)"
            balanced=0
            break
        fi
    done < <(grep -n "@if\|@endif" "$file")
    
    if [ "$depth" -ne 0 ] && [ "$balanced" -eq 1 ]; then
        echo -e "${RED}❌ $file${NC}"
        echo "   Unclosed @if blocks: depth=$depth at end of file"
        # Show the last few @if statements
        echo "   Last @if statements:"
        grep -n "@if" "$file" | tail -3
    fi
done

echo -e "\n${GREEN}Check complete.${NC}"
