<?php
// CONFIGURATION
$to_email = "kfrlodge208@gmail.com"; // <--- ENTER YOUR EMAIL HERE
$upload_dir = "uploads/"; // The folder where CVs will be saved

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Collect and Sanitize Text Inputs
    $fullname = htmlspecialchars(strip_tags($_POST['fullname']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(strip_tags($_POST['phone']));
    $position = htmlspecialchars(strip_tags($_POST['position']));
    $experience = htmlspecialchars(strip_tags($_POST['experience']));

    // 2. Handle File Upload
    $upload_status = "No file uploaded.";
    $file_path = "";

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        
        // Check if 'uploads' folder exists, if not, create it
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_name = basename($_FILES["resume"]["name"]);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file type (Security)
        $allowed_types = array("pdf", "doc", "docx");
        
        if (in_array($file_type, $allowed_types)) {
            // Generate unique name to prevent overwriting: "timestamp_filename"
            $new_file_name = time() . "_" . $file_name;
            $target_file = $upload_dir . $new_file_name;

            if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
                $upload_status = "Success";
                // Create a link to the file for the email
                // NOTE: Change 'yourwebsite.com' to your actual website domain
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $host = $_SERVER['HTTP_HOST'];
                $file_url = "$protocol://$host/" . dirname($_SERVER['PHP_SELF']) . "/" . $target_file;
            } else {
                $upload_status = "Failed to move file.";
            }
        } else {
            echo "Error: Only PDF, DOC, and DOCX files are allowed.";
            exit;
        }
    }

    // 3. Construct Email Content
    $subject = "New Job Application: " . $fullname . " (" . ucfirst($position) . ")";
    
    $message = "New Job Application Received!\n\n";
    $message .= "Name: " . $fullname . "\n";
    $message .= "Email: " . $email . "\n";
    $message .= "Phone: " . $phone . "\n";
    $message .= "Position: " . ucfirst($position) . "\n";
    $message .= "Experience Summary:\n" . $experience . "\n\n";
    $message .= "-------------------------\n";
    
    if ($upload_status == "Success") {
        $message .= "RESUME LINK: Please click below to view the CV:\n";
        $message .= $file_url . "\n";
    } else {
        $message .= "Resume Upload Status: " . $upload_status . "\n";
    }

    // 4. Send Email
    $headers = "From: no-reply@kfrlodge.com" . "\r\n" .
               "Reply-To: " . $email . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    if (mail($to_email, $subject, $message, $headers)) {
        // Success: Redirect to a Thank You page or show message
        echo "<h1>Application Sent Successfully!</h1>";
        echo "<p>Thank you, $fullname. We have received your application for the $position position.</p>";
        echo "<a href='javascript:history.back()'>Return to Homepage</a>";
    } else {
        echo "<h1>Error sending application.</h1>";
        echo "<p>Please try again later or contact us directly.</p>";
    }

} else {
    echo "Access Denied.";
}
?>