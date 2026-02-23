<?php
$pageTitle = "Code Playground";

$extraCSS = '
    <style>
        .playground-container {
            max-width: 900px;
            margin: 2rem auto;
            margin-left: 10%;
            padding: 2rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .playground-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .playground-header h1 {
            font-size: 2rem;
            color: #333;
        }

        .editor-container,
        .output-container {
            margin-bottom: 1.5rem;
        }

        .editor-container textarea,
        .output-container pre {
            width: 100%;
            height: 300px;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-family: Courier, monospace;
            font-size: 2rem;
            resize: none;
        }

        .editor-container textarea {
            background-color: #f9f9f9;
        }

        .output-container pre {
            background-color: lightslategrey;
            color: #f8f8f2;
            overflow: auto;
        }

        .button-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .run-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            color: #fff;
            background-color: #28a745;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .run-btn:hover {
            background-color: #218838;
        }
    </style> ';

include '../includes/header.php';
?>


<section class="playground-container">
    <div class="playground-header">
        <h1>Code Playground</h1>
    </div>
    <div class="editor-container">
        <textarea id="code-editor" placeholder="Write your code here..."></textarea>
    </div>
    <div class="button-container">
        <button class="run-btn" id="run-code-btn">Run Code</button>
    </div>
    <div class="output-container">
        <pre id="code-output"></pre>
    </div>
</section>
<?php
$extraJS = '
<script>
document.getElementById("run-code-btn").addEventListener("click", () => {
    const userCode = document.getElementById("code-editor").value;
    const output = document.getElementById("code-output");

    const iframe = document.createElement("iframe");
    iframe.style.width="100%";
    iframe.style.height="300px";
    iframe.style.border="none";

    output.innerHTML="";
    output.appendChild(iframe);

    const doc = iframe.contentDocument;
    doc.open();
    doc.write(userCode);
    doc.close();
});
</script>
';

include '../includes/footer.php';
?>