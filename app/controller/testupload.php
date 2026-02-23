<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Upload and Question Generation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f9;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }
        input[type="file"] {
            display: block;
            margin-bottom: 20px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        #output {
            margin: 20px auto;
            max-width: 500px;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .field {
            margin-bottom: 10px;
        }
        .field label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Upload PDF and Generate Question</h1>
    <form id="uploadForm" enctype="multipart/form-data">
        <label for="document">Select a PDF File:</label>
        <input type="file" id="document" name="document" accept="application/pdf" required>
        <button type="submit">Upload and Generate Question</button>
    </form>

    <!-- Output Fields -->
    <div id="output">
        <div class="field">
            <label>Generated Question:</label>
            <div id="question">N/A</div>
        </div>
        <div class="field">
            <label>Content:</label>
            <div id="content">N/A</div>
        </div>
        <div class="field">
            <label>Answer:</label>
            <div id="answer">N/A</div>
        </div>
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', async (event) => {
    event.preventDefault(); // Prevent form submission and page reload
    const formData = new FormData(event.target);
    const outputContainer = document.getElementById('output');

    // Clear previous output
    document.getElementById('question').textContent = 'N/A';
    document.getElementById('content').textContent = 'N/A';
    document.getElementById('answer').textContent = 'N/A';

    try {
        // Send the file to the backend
        const response = await fetch('process_generate_questions.php', {
            method: 'POST',
            body: formData,
        });

        if (!response.ok) {
            const errorText = await response.text();
            outputContainer.innerHTML = `<div class="error">Failed to generate questions: ${errorText}</div>`;
            return;
        }

        const result = await response.json();
        console.log('Generated Result:', result);

        // Populate the fields with the response
        document.getElementById('question').textContent = result.question;
        document.getElementById('content').textContent = result.content;
        document.getElementById('answer').textContent = result.answer;

    } catch (error) {
        console.error('Error:', error);
        document.getElementById('output').innerHTML = `<div class="error">An unexpected error occurred. Please try again.</div>`;
    }
});

    </script>
</body>
</html>
