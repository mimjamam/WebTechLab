<?php

$conn = mysqli_connect('localhost', 'root', '', 'app_users');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// Validation functions
function validateName($name) {
    // Trim and split the name into parts
    $nameParts = explode(" ", trim($name));
    
    // Ensure there is at least one part and the first character of the first part is uppercase
    if (count($nameParts) < 1 || !isset($nameParts[0][0]) || !ctype_upper($nameParts[0][0])) {
        return false;
    }
    
    // Check if any special character other than '.' exists
    if (preg_match('/[^a-zA-Z. ]/', $name)) {
        return false;
    }
    
    // Ensure the second part, if it exists, starts with an uppercase letter
    if (isset($nameParts[1]) && (!isset($nameParts[1][0]) || !ctype_upper($nameParts[1][0]))) {
        return false;
    }
    
    return true;
}


function validateID($id) {
    return preg_match("/^\\d{2}-\\d{5}-\\d$/", $id);
}

function validateEmail($email) {
    return preg_match("/^\\d{2}-\\d{5}-\\d@student\\.aiub\\.edu$/", $email);
}

// Fetch POST data and initialize error messages
$fullname = $_POST['fullname'];
$aiub_id = $_POST['aiub_id'];
$aiub_email = $_POST['aiub_email'];
$books = isset($_POST['books']) ? $_POST['books'] : null;
$borrow_date = $_POST['borrow_date'];
$token = $_POST['token'];
$return_date = $_POST['return_date'];
$fee = $_POST['fee'];
// Save token value to usedtoken.json
$tokenFile = 'usedtoken.json';
$tokens = [];

$errors = [];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate fields
    if (empty($fullname)) {
        $errors[] = "Full Name is required.";
    } elseif (!validateName($fullname)) {
        $errors[] = "Invalid Full Name. Each part must start with a capital letter and can only include letters, spaces, and periods.";
    }
    
    if (empty($aiub_id)) {
        $errors[] = "AIUB ID is required.";
    } elseif (!validateID($aiub_id)) {
        $errors[] = "Invalid AIUB ID. It should be in the format XX-XXXXX-X.";
    }

    if (empty($aiub_email)) {
        $errors[] = "Email is required.";
    } elseif (!validateEmail($aiub_email)) {
        $errors[] = "Invalid Email. It should follow the format id@student.aiub.edu with the correct ID pattern.";
    }

    if (empty($books)) {
        $errors[] = "At least one book must be selected.";
    }

    if (empty($borrow_date)) {
        $errors[] = "Borrow Date is required.";
    }

    // Compare token with usedtoken.json
    $usedTokenFile = 'usedtoken.json';
    if (file_exists($usedTokenFile)) {
        $usedTokens = json_decode(file_get_contents($usedTokenFile), true);
        if (is_array($usedTokens) && in_array($token, $usedTokens)) {
            $errors[] = "Someone has already used this token.";
        }
    } else {
        $usedTokens = []; // Initialize if file doesn't exist
    }
// Token validation
$tokenFile = 'token.json';
$tokens = json_decode(file_get_contents($tokenFile), true); // Decode the token file

$isValidToken = false;

// Check if the token exists and is valid
if (!empty($token) && isset($tokens['token']) && in_array($token, $tokens['token'])) {
    $isValidToken = true;
    // Remove the token from the file after processing
    $tokens['token'] = array_diff($tokens['token'], [$token]);
    file_put_contents($tokenFile, json_encode($tokens, JSON_PRETTY_PRINT));
}

$borrowTimestamp = strtotime($borrow_date);
$returnTimestamp = strtotime($return_date);
$dateDifference = ($returnTimestamp - $borrowTimestamp) / (60 * 60 * 24);

if ($dateDifference > 10) {
    // Only check token validity if borrow period is more than 10 days
    if (!$isValidToken) {
        $errors[] = "Valid token is required for a borrowing period greater than 10 days.";
    }
} elseif ($dateDifference <= -1) {
    $errors[] = "Return Date must be after the Borrow Date.";
}

// Cookie-based book borrowing restriction
if (is_array($books)) {
    foreach ($books as $book) {
        $cookieName = str_replace([' ', '-', '='], '_', $book);
        if (isset($_COOKIE[$cookieName])) {
            if ($_COOKIE[$cookieName] === $fullname) {
                $errors[] = "You already borrowed '$book'.";
            } else {
                $errors[] = "'$book' is already borrowed by someone else.";
            }
        }
    }
} else {
    $cookieName = str_replace([' ', '-', '='], '_', $books);
    if (isset($_COOKIE[$cookieName])) {
        if ($_COOKIE[$cookieName] === $fullname) {
            $errors[] = "You already borrowed '$books'.";
        } else {
            $errors[] = "'$books' is already borrowed by someone else.";
        }
    }
}


    // If there are no errors, set cookies and save token to usedtoken.json
    if (empty($errors)) {
        if (is_array($books)) {
            foreach ($books as $book) {
                $cookieName = str_replace([' ', '-', '='], '_', $book);
                setcookie($cookieName, $fullname, time() + 60, "/");
            }
        } else {
            $cookieName = str_replace([' ', '-', '='], '_', $books);
            setcookie($cookieName, $fullname, time() + 60, "/");
        }
    
        // Save the used token to usedtoken.json
        if (!empty($token)) {
            if (file_exists($usedTokenFile)) {
                $usedTokens = json_decode(file_get_contents($usedTokenFile), true);
                if (!is_array($usedTokens)) {
                    $usedTokens = [];
                }
            } else {
                $usedTokens = [];
            }
            $usedTokens[] = $token;
            file_put_contents($usedTokenFile, json_encode($usedTokens, JSON_PRETTY_PRINT));
        }
    
        // Convert books array to string if needed
        $booksString = is_array($books) ? implode(", ", $books) : $books;
    
        // Insert borrowing data into the database
        $borrow_query = "INSERT INTO borrows (fullname, aiub_id, aiub_email, books, borrow_date, return_date, fee, token)
                         VALUES ('$fullname', '$aiub_id', '$aiub_email', '$booksString', '$borrow_date', '$return_date', '$fee', '$token')";
    
        if (mysqli_query($conn, $borrow_query)) {
            // Redirect to receipt page after successful insertion
            header("Location: receipt.php?fullname=$fullname&aiub_id=$aiub_id&aiub_email=$aiub_email&books=$booksString&borrow_date=$borrow_date&return_date=$return_date&token=$token&fee=$fee");
            exit();
        } else {
            echo "<p>Error saving data: " . mysqli_error($conn) . "</p>";
        }
    }
    
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Error Page</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Orbitron', sans-serif;
            background: linear-gradient(135deg, #0f0f0f, #363636);
            color: #cccccc;
        }
        .error-container {
            text-align: center;
            padding: 30px;
            border: 2px solid #ff6347;
            border-radius: 15px;
            background: #1a1a1a;
            box-shadow: 0px 10px 30px rgba(255, 255, 255, 0.1);
            max-width: 400px;
            color: #ff6347;
        }
        .error-message {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
            line-height: 1.6;
        }
        .back-link {
            margin-top: 20px;
            display: inline-block;
            text-decoration: none;
            color: #007bff;
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 5px;
            background: #252525;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .back-link:hover {
            color: #ffffff;
            background: #007bff;
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.2);
            text-decoration: none;
        }
    </style>
</head>
<body>
<?php if (!empty($errors)) : ?>
    <div class="error-container">
        <?php foreach ($errors as $error) : ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endforeach; ?>
        <a href="index.php" class="back-link">Go Back to the Form</a>
    </div>
<?php endif; ?>
</body>  
</html>
