#!/bin/bash

echo "Accurate count - @if(with parenthesis) and variations..."
echo "========================================================"

files=(
  "resources/views/layouts/app.blade.php"
  "resources/views/layouts/guest.blade.php"
  "resources/views/convert/create.blade.php"
  "resources/views/convert/history.blade.php"
  "resources/views/convert/show.blade.php"
  "resources/views/dashboard/index.blade.php"
  "resources/views/files/index.blade.php"
  "resources/views/files/show.blade.php"
  "resources/views/notifications/index.blade.php"
  "resources/views/plans/index.blade.php"
  "resources/views/profile/edit.blade.php"
  "resources/views/admin/dashboard.blade.php"
  "resources/views/admin/plans/form.blade.php"
  "resources/views/admin/settings/index.blade.php"
  "resources/views/admin/users/index.blade.php"
)

for file in "${files[@]}"; do
  # Count @if with any form (with or without space/parenthesis)
  if_count=$(grep -o '@if' "$file" | wc -l)
  endif_count=$(grep -o '@endif' "$file" | wc -l)
  elseif_count=$(grep -o '@elseif' "$file" | wc -l)
  else_count=$(grep -o '@else\b' "$file" | wc -l)
  
  if [ "$if_count" -ne "$endif_count" ]; then
    echo -e "\n❌ $file"
    echo "   @if:     $if_count"
    echo "   @elseif: $elseif_count"
    echo "   @else:   $else_count"
    echo "   @endif:  $endif_count"
  fi
done

echo -e "\n\nFiles with BALANCED directives:"
echo "================================="

for file in "${files[@]}"; do
  if_count=$(grep -o '@if' "$file" | wc -l)
  endif_count=$(grep -o '@endif' "$file" | wc -l)
  elseif_count=$(grep -o '@elseif' "$file" | wc -l)
  else_count=$(grep -o '@else\b' "$file" | wc -l)
  
  # For if blocks: @if = @endif, and @elseif/@else should be within those
  if [ "$if_count" -eq "$endif_count" ]; then
    echo -e "✓ $file (@if: $if_count, @endif: $endif_count, @elseif: $elseif_count, @else: $else_count)"
  fi
done

