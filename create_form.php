<?php
// create_form.php
// We assume there's no user authentication for admin, but in production you should add it.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Form</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .field-config { border: 1px solid #ccc; padding: 10px; margin: 10px 0; }
        .options { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create New Form</h1>
        <form action="save_form.php" method="POST">
            <div class="form-group">
                <label for="title">Form Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Form Description</label>
                <textarea id="description" name="description"></textarea>
            </div>

            <h3>Form Fields</h3>
            <div id="fields-container">
                <!-- Dynamic fields will be added here -->
            </div>
            <button type="button" id="add-field" class="btn">Add Field</button>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn">Create Form</button>
            </div>
        </form>
    </div>

    <script>
        let fieldCount = 0;

        document.getElementById('add-field').addEventListener('click', function() {
            fieldCount++;
            const fieldHTML = `
                <div class="field-config">
                    <div class="form-group">
                        <label>Field Type</label>
                        <select name="fields[${fieldCount}][type]" onchange="toggleOptions(this)" required>
                            <option value="">Select Type</option>
                            <option value="text">Text</option>
                            <option value="email">Email</option>
                            <option value="textarea">Textarea</option>
                            <option value="number">Number</option>
                            <option value="select">Select</option>
                            <option value="radio">Radio</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="rating">Rating</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Label</label>
                        <input type="text" name="fields[${fieldCount}][label]" required>
                    </div>
                    <div class="form-group">
                        <label>Placeholder</label>
                        <input type="text" name="fields[${fieldCount}][placeholder]">
                    </div>
                    <div class="form-group options" id="options-${fieldCount}">
                        <label>Options (one per line)</label>
                        <textarea name="fields[${fieldCount}][options]"></textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="fields[${fieldCount}][required]"> Required
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="fields[${fieldCount}][sort_order]" value="${fieldCount}">
                    </div>
                    <button type="button" class="btn btn-danger" onclick="removeField(this)">Remove</button>
                </div>
            `;
            document.getElementById('fields-container').insertAdjacentHTML('beforeend', fieldHTML);
        });

        function toggleOptions(select) {
            const fieldConfig = select.closest('.field-config');
            const optionsDiv = fieldConfig.querySelector('.options');
            const type = select.value;
            if (type === 'select' || type === 'radio' || type === 'checkbox') {
                optionsDiv.style.display = 'block';
            } else {
                optionsDiv.style.display = 'none';
            }
        }

        function removeField(button) {
            button.closest('.field-config').remove();
        }
    </script>
</body>
</html>