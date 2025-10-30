invoices/resources/views/test-searchable.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Searchable Select Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .searchable-select-option.focused {
            background-color: #f3f4f6 !important;
            font-weight: 500 !important;
            color: #111827 !important;
        }
    </style>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Searchable Select Test</h1>

        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4">Test Select</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Regular Select (Should be converted to searchable)</label>
                <select name="test_item"
                        id="test-item"
                        class="searchable-select"
                        onchange="updateTestItem()">
                    <option value="">Choose an item</option>
                    @foreach($testItems as $item)
                        <option value="{{ $item['id'] }}"
                                data-price="{{ $item['unit_price'] }}"
                                data-description="{{ $item['description'] }}">
                            {{ $item['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Output</label>
                <div id="output" class="p-3 bg-gray-100 rounded text-sm"></div>
            </div>

            <div class="mb-4">
                <button type="button"
                        onclick="addNewTestItem()"
                        class="px-4 py-2 bg-primary-500 text-white rounded hover:bg-primary-600">
                    Add Dynamic Item
                </button>
            </div>

            <div id="dynamic-container"></div>
        </div>
    </div>

    <script>
        function updateTestItem() {
            const select = document.getElementById('test-item');
            const output = document.getElementById('output');

            if (select.value) {
                const selectedOption = select.options[select.selectedIndex];
                const price = selectedOption.getAttribute('data-price');
                const description = selectedOption.getAttribute('data-description');

                output.innerHTML = `
                    <strong>Selected:</strong> ${selectedOption.text}<br>
                    <strong>Price:</strong> $${price}<br>
                    <strong>Description:</strong> ${description}
                `;
            } else {
                output.innerHTML = 'No item selected';
            }
        }

        function addNewTestItem() {
            const container = document.getElementById('dynamic-container');
            const itemCount = container.children.length + 1;

            const itemHtml = `
                <div class="mb-4 p-4 border border-gray-200 rounded">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dynamic Select #${itemCount}</label>
                    <select name="dynamic_items[${itemCount}][item_id]"
                            class="searchable-select"
                            onchange="updateTestItem()">
                        <option value="">Choose an item</option>
                        <option value="test1" data-price="50" data-description="Test item 1">Test Item 1</option>
                        <option value="test2" data-price="75" data-description="Test item 2">Test Item 2</option>
                        <option value="test3" data-price="125" data-description="Test item 3">Test Item 3</option>
                    </select>
+                </div>
+            `;
+
+            container.insertAdjacentHTML('beforeend', itemHtml);
+
+            // Initialize searchable select for the newly added item
+            setTimeout(() => {
+                if (window.initSearchableSelectsForNewContent) {
+                    initSearchableSelectsForNewContent();
+                }
+            }, 100);
+        }
+
+        // Test the initialization
+        document.addEventListener('DOMContentLoaded', function() {
+            // Check if the function exists and works
+            if (window.initSearchableSelects) {
+                console.log('✅ initSearchableSelects function exists');
+
+                // Initialize searchable selects
+                initSearchableSelects();
+
+                // Test that we can find our test select
+                setTimeout(() => {
+                    const testSelect = document.getElementById('test-item');
+                    const wrapper = testSelect.closest('.searchable-select-wrapper');
+
+                    if (wrapper) {
+                        console.log('✅ Searchable select wrapper created successfully');
+                        console.log('Wrapper:', wrapper);
+
+                        const display = wrapper.querySelector('.searchable-select-display');
+                        const searchInput = wrapper.querySelector('.searchable-select-input');
+                        const dropdown = wrapper.querySelector('.searchable-select-dropdown');
+
+                        console.log('Display:', display);
+                        console.log('Search input:', searchInput);
+                        console.log('Dropdown:', dropdown);
+
+                        // Test click on display to show dropdown
+                        if (display) {
+                            display.addEventListener('click', () => {
+                                console.log('Display clicked!');
+                                searchInput?.focus();
+                            });
+                        }
+                    } else {
+                        console.log('❌ No searchable wrapper found - select not initialized');
+                        console.log('Original select:', testSelect);
+                        console.log('Has searchable-select class:', testSelect.classList.contains('searchable-select'));
+                        console.log('Has initialized flag:', testSelect.classList.contains('searchable-select-initialized'));
+                    }
+                }, 200);
+            } else {
+                console.log('❌ initSearchableSelects function not found');
+                console.log('Available functions:', Object.keys(window));
+            }
+        });
+    </script>
+</body>
+</html>
