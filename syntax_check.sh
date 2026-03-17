#!/bin/bash

echo "COMPREHENSIVE BLADE SYNTAX ANALYSIS REPORT"
echo "=========================================="

echo -e "\n1. FILE LISTING"
echo "==============="
echo "Total blade files:"
find resources/views -name "*.blade.php" -type f | wc -l
echo "All files:"
find resources/views -name "*.blade.php" -type f | sort

echo -e "\n2. DIRECTIVE PAIR BALANCE SUMMARY"
echo "=================================="
echo "Checking: @if/@endif, @foreach/@endforeach, @forelse/@endforelse, @section/@endsection"

find resources/views -name "*.blade.php" -type f | sort | while read file; do
    if_cnt=$(grep -o '@if' "$file" | wc -l)
    endif_cnt=$(grep -o '@endif' "$file" | wc -l)
    foreach_cnt=$(grep -o '@foreach' "$file" | wc -l)
    endforeach_cnt=$(grep -o '@endforeach' "$file" | wc -l)
    forelse_cnt=$(grep -o '@forelse' "$file" | wc -l)
    endforelse_cnt=$(grep -o '@endforelse' "$file" | wc -l)
    section_cnt=$(grep -o '@section' "$file" | wc -l)
    endsection_cnt=$(grep -o '@endsection' "$file" | wc -l)
    php_cnt=$(grep -o '@php' "$file" | wc -l)
    endphp_cnt=$(grep -o '@endphp' "$file" | wc -l)
    push_cnt=$(grep -o '@push' "$file" | wc -l)
    endpush_cnt=$(grep -o '@endpush' "$file" | wc -l)
    
    errors=0
    [ "$if_cnt" -ne "$endif_cnt" ] && errors=1
    [ "$foreach_cnt" -ne "$endforeach_cnt" ] && errors=1
    [ "$forelse_cnt" -ne "$endforelse_cnt" ] && errors=1
    [ "$push_cnt" -ne "$endpush_cnt" ] && errors=1
    [ "$php_cnt" -ne "$endphp_cnt" ] && errors=1
    
    # Note: @section/@endsection can have mismatches because of inline declarations
    
    if [ "$errors" -eq 1 ]; then
        echo "❌ $(basename $file)"
        [ "$if_cnt" -ne "$endif_cnt" ] && echo "   @if:$if_cnt vs @endif:$endif_cnt"
        [ "$foreach_cnt" -ne "$endforeach_cnt" ] && echo "   @foreach:$foreach_cnt vs @endforeach:$endforeach_cnt"
        [ "$forelse_cnt" -ne "$endforelse_cnt" ] && echo "   @forelse:$forelse_cnt vs @endforelse:$endforelse_cnt"
        [ "$push_cnt" -ne "$endpush_cnt" ] && echo "   @push:$push_cnt vs @endpush:$endpush_cnt"
        [ "$php_cnt" -ne "$endphp_cnt" ] && echo "   @php:$php_cnt vs @endphp:$endphp_cnt"
    fi
done

echo -e "\n3. INCLUDE AND COMPONENT DIRECTIVES"
echo "===================================="
echo "Files with @include directives:"
grep -r "@include" resources/views/ --include="*.blade.php" | wc -l
if grep -r "@include" resources/views/ --include="*.blade.php" > /dev/null; then
    grep -r "@include" resources/views/ --include="*.blade.php"
else
    echo "  (none found)"
fi

echo -e "\nFiles with @component directives:"
grep -r "@component" resources/views/ --include="*.blade.php" | wc -l
if grep -r "@component" resources/views/ --include="*.blade.php" > /dev/null; then
    grep -r "@component" resources/views/ --include="*.blade.php"
else
    echo "  (none found)"
fi

echo -e "\n4. LAYOUTS AND EXTENDS"
echo "======================"
grep -r "@extends" resources/views/ --include="*.blade.php"

echo -e "\n5. COMMON MALFORMED DIRECTIVES TO CHECK"
echo "========================================="

# Check for @endif without @ or mistyped
echo "Checking for obvious typos..."
grep -rn "endif\|endfor\|endphp\|endsection" resources/views/ --include="*.blade.php" | grep -v "@endif\|@endfor\|@endphp\|@endsection" | head -10

