<?php
session_start();

// Check if the user is authenticated
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    // Redirect to the login page if not authenticated
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>Backup Database v2</title>
        <meta charset="utf-8" />
        <meta owner="thomas vincent">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
        <link rel="stylesheet" href="assets/css/main.css" />
        <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
        <script>
           function typeWriterEffect(element, text, speed, callback) {
    let i = 0;
    const typewriter = setInterval(function () {
        if (i < text.length) {
            element.innerHTML += text.charAt(i);
            i++;
        } else {
            clearInterval(typewriter);
            if (callback) {
                callback(); // Call the callback function after typing is complete
            }
        }
    }, speed);
}

function backupDatabase(databaseName) {
    // Disable the button to prevent multiple clicks
    var backupButton = document.getElementById(`backupButton_${databaseName}`);
    backupButton.disabled = true;

    // Display the loading indicator with typing effect
    var loadingIndicator = document.getElementById(`loadingIndicator_${databaseName}`);
    loadingIndicator.style.display = 'inline-block';

    // Typing effect for the loading message
    typeWriterEffect(loadingIndicator, "Proses backup sedang berlangsung mohon ditunggu ...", 50, function () {
        // Send an AJAX request to backup_script.php
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "backup_script.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.responseType = 'blob';  // Set the response type to blob

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                // Re-enable the button and hide the loading indicator
                backupButton.disabled = false;
                loadingIndicator.style.display = 'none';

                if (xhr.status == 200) {
                    // Create a Blob from the response
                    var blob = new Blob([xhr.response], { type: 'application/octet-stream' });

                    // Create a link element to trigger the download
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = `backup_${databaseName}_${new Date().toISOString().replace(/[:.]/g, "_")}.sql`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    // Update the loading message to indicate the backup is complete
                    var backupStatus = document.getElementById(`backupStatus_${databaseName}`);
                    backupStatus.innerHTML = "Proses backup selesai, mohon tunggu sebentar akan download otomatis ....";
                } else {
                    // Handle errors here
                    console.error("Error in backup: " + xhr.statusText);
                }
            }
        };
        xhr.send("database=" + databaseName);
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const emptyMessage = document.getElementById("emptyMessage");
    if (emptyMessage) {
        typeWriterEffect(emptyMessage, "Data Riwayat Backup Masih Kosong ...", 50);
    }
});
document.addEventListener("DOMContentLoaded", function () {
    const emptyMessage1 = document.getElementById("emptyMessage1");
    if (emptyMessage1) {
        typeWriterEffect(emptyMessage1, "Data Riwayat Backup Masih Kosong ...", 50);
    }
});
document.addEventListener("DOMContentLoaded", function () {
    const emptyMessage2 = document.getElementById("emptyMessage2");
    if (emptyMessage2) {
        typeWriterEffect(emptyMessage2, "Data Riwayat Backup Masih Kosong ...", 50);
    }
});

        </script>
    </head>
    <body class="is-preload">

        <!-- Wrapper-->
        <div id="wrapper">

            <!-- Nav -->
            <nav id="nav">
                <a href="#" class="icon solid fa-database"><span>Backup</span></a>
                <a href="#work" class="icon solid fa-history"><span>History</span></a>
                <a href="logout.php" class="icon solid fa-lock"><span>Log Out</span></a>
            </nav>

            <!-- Main -->
            <div id="main">
                <!-- Me -->
                <article id="home" class="panel intro">
                    <header>
                        <p>
                            <?php
                            // Menampilkan daftar database
                            $servername = "127.0.0.1";
                            $username = "root";
                            $password = "";

                            // Membuat koneksi
                            $conn = new mysqli($servername, $username, $password);

                            // Memeriksa koneksi
                            if ($conn->connect_error) {
                                die("Koneksi gagal: " . $conn->connect_error);
                            }

                            // Menjalankan query untuk menampilkan database
                            $query = "SHOW DATABASES";
                            $result = $conn->query($query);

                            // Memeriksa hasil query
                            if ($result) {
                                echo "<h2><center>- Backup Database -</center></h2>";
                                echo "<center><div class='database-columns'>";
								
								// Function to get the last backup time from the log file
							function getLastBackupTimeFromLog($databaseName) {
								// Replace this with the actual path to your log file
								$logFilePath = "log/logfile.txt";

								// Read the log file content
								if (file_exists($logFilePath)) {
									$logContent = file_get_contents($logFilePath);

									// Create a pattern to match the last backup entry for the given database
									$pattern = "/{$databaseName} \| (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}|\d{4}-\d{2}-\d{2}_\d{2}:\d{2}:\d{2}) \|/";

									// Match all occurrences of the pattern
									preg_match_all($pattern, $logContent, $matches);

									// Check if there are any matches
									if (isset($matches[1]) && is_array($matches[1]) && count($matches[1]) > 0) {
										// Extract the last match (last backup time)
										$lastBackupTime = end($matches[1]);
									} else {
										echo "Error: No matching backup entry found for database {$databaseName}.";
										// Return a default value
										$lastBackupTime = "Not available";
									}

									return $lastBackupTime;
								} else {
									echo "Error: Riwayat Backup belum ada ...";
									// Return a default value
									return "Tidak tersedia";
								}
							}								
                                // Menampilkan nama-nama database dengan tombol backup
                                while ($row = $result->fetch_assoc()) {
                                    $databaseName = $row['Database'];

                                    // Check if the database name is 'ars' or 'sadardjaya'
                                    if ($databaseName == 'nama_db_1' || $databaseName == 'nama_db_2') {
                                        $lastBackupTime = getLastBackupTimeFromLog($databaseName);
										echo "<div class='database-column'>";
										echo "<span style='font-weight: bold; color: black; font-size: medium'>{$databaseName} (Backup Terakhir : {$lastBackupTime}) </span>";
                                        // Create a div to display the backup status
                                        echo "<div id='backupStatus_{$databaseName}' style='font-weight: bold; color: black; font-size: small;'></div>";
                                        // Create a loading indicator
                                        echo "<div id='loadingIndicator_{$databaseName}' style='font-weight: bold; color: black; display: none; font-size: small;'>Loading... </div>";
                                        // Call the JavaScript function on button click
                                        echo "<button id='backupButton_{$databaseName}' class='btn' onclick='backupDatabase(\"{$databaseName}\")'>Backup Now →</button>";
										echo "<hr>";
                                        echo "</div>";
                                    }
                                }
                                echo "</div></center>";

                                // Menutup hasil query
                                $result->close();
                            } else {
                                echo "Error: " . $conn->error;
                            }

                            // Menutup koneksi
                            $conn->close();
                            ?>
                        </p>
                    </header>
                    <a href="#work" class="jumplink pic">
                        <span class="arrow icon solid fa-chevron-right"><span>See my work</span></span>
                        <img src="assets/css/images/backup.jpg" alt="" />
                    </a>
                </article>

                <!-- Work -->
                <article id="work" class="panel">
                    <header>
                        <center><h2>-History Backup -</h2></center>
                    </header>
                    <p>
					<?php
        // Waktu backup terakhir
        $lastBackupLog = "log/logfile.txt"; // Sesuaikan dengan lokasi file log
        if (file_exists($lastBackupLog)) {
            $lastBackupTime = file_get_contents($lastBackupLog);
			$logLines = file($lastBackupLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);          
            // echo "<h2>Waktu Backup Terakhir:</h2>";
            // if (empty($logLines)) {
            //     echo "<p id='emptyMessage'></p>";
            // } else {
            // echo "<p>" . nl2br($lastBackupTime) . "</p>";
            // }

            // Tombol Download untuk Riwayat Backup
            if (empty($logLines)) {
                echo "<p id='emptyMessage1'></p>";
            } else {
            echo "<form action='download_history.php' method='post'>";
            echo "<input type='hidden' name='log_file' value='{$lastBackupLog}'>";
            echo "<center><input type='submit' value='Download Riwayat Backup'></center>";
            echo "</form>";
            }

            // Menampilkan history backup dalam tabel
			if (empty($logLines)) {
				echo "<p id='emptyMessage2'></p>";
			} else {
				echo "<table style='border-collapse: collapse; width: 100%; border: 1px solid black; color: black;' border='1'>
						<tr>
							<th style='border: 1px solid black; text-align: left; padding: 8px;'>No</th>
							<th style='border: 1px solid black; text-align: left; padding: 8px;'>Database</th>
							<th style='border: 1px solid black; text-align: left; padding: 8px;'>Waktu Backup</th>
							<th style='border: 1px solid black; text-align: left; padding: 8px;'>File Backup</th>
						</tr>";

				$counter = 1;

				foreach ($logLines as $logLine) {
					// Parsing data dari baris log
					$logData = explode("|", $logLine);

					// Check if indices exist before accessing them
					$database = isset($logData[0]) ? trim($logData[0]) : "";
					$timestamp = isset($logData[1]) ? trim($logData[1]) : "";
					$backupFileName = isset($logData[2]) ? trim($logData[2]) : "";

					// Menampilkan data dalam tabel
					echo "<tr>
						<td style='border: 1px solid black; text-align: left; padding: 8px; font-size: medium; color: black !important;'><span style='color: black;'>{$counter}</span></td>
						<td style='border: 1px solid black; text-align: left; padding: 8px; font-size: medium; color: black !important;'>{$database}</td>
						<td style='border: 1px solid black; text-align: left; padding: 8px; font-size: medium; color: black !important;'>{$timestamp}</td>
						<td style='border: 1px solid black; text-align: left; padding: 8px; font-size: medium;'><a href='db/{$backupFileName}' style='color: blue !important;' download>→ Click To Download ←</a></td>
					</tr>";



					$counter++;
				}

				echo "</table>";
			}
        } else {
            echo "<p>Belum ada backup yang dilakukan.</p>";
        }
    ?>
                    </p>
                </article>

                <!-- Contact -->
                <article id="contact" class="panel">
                    <header>
                        <h2>Contact Me</h2>
                    </header>
                    <form action="#" method="post">
                        <div>
                            <div class="row">
                                <div class="col-6 col-12-medium">
                                    <input type="text" name="name" placeholder="Name" />
                                </div>
                                <div class="col-6 col-12-medium">
                                    <input type="text" name="email" placeholder="Email" />
                                </div>
                                <div class="col-12">
                                    <input type="text" name="subject" placeholder="Subject" />
                                </div>
                                <div class="col-12">
                                    <textarea name="message" placeholder="Message" rows="6"></textarea>
                                </div>
                                <div class="col-12">
                                    <input type="submit" value="Send Message" />
                                </div>
                            </div>
                        </div>
                    </form>
                </article>
            </div>

            <!-- Footer -->
            <div id="footer">
                <ul class="copyright">
                    <li>&copy; 2023.</li><li>♥ Coded: <a href="https://www.facebook.com/ThomsVnct/">Thomas Vincent</a></li>
                </ul>
            </div>
        </div>

        <!-- Scripts -->
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/browser.min.js"></script>
        <script src="assets/js/breakpoints.min.js"></script>
        <script src="assets/js/util.js"></script>
        <script src="assets/js/main.js"></script>
    </body>
</html>