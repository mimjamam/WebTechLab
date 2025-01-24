<?php
// Get the query parameters from the URL
$fullname = $_GET['fullname'];
$aiub_id = $_GET['aiub_id'];
$aiub_email = $_GET['aiub_email'];
$books = $_GET['books'];
$borrow_date = $_GET['borrow_date'];
$return_date = $_GET['return_date'];
$token = $_GET['token'];
$fee = $_GET['fee'];


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&display=swap" rel="stylesheet">
    <title>Library Receipt</title>
    <style>
       body {
        font-family: 'Orbitron', sans-serif; /* A font that often conveys a techy, futuristic feel */
        background: linear-gradient(135deg, #1a1a1a, #2c2c2c); /* Darker gradient for a more high-tech look */
        color: #ccc; /* Light text on dark backgrounds */
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        padding: 20px;
        }

    .receipt-container {
        background: #121212; /* Dark background for the container */
        box-shadow: 0px 10px 30px rgba(255, 255, 255, 0.1); /* White glow for a more futuristic look */
        border-radius: 10px;
        padding: 25px;
        width: 100%;
        max-width: 500px;
        text-align: center;
        border: 1px solid #333; /* Subtle border */
        }

    .receipt-container h2 {
        color: #00bfff; /* Bright blue for a high-tech feel */
        font-size: 28px;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 2px; /* Increase letter spacing for a more digital look */
    }

    .receipt-container p {
        font-size: 16px;
        color: #cccccc; /* Light gray text for better readability on dark backgrounds */
        margin: 8px 0;
    }

    .receipt-container strong {
        color: #ffffff; /* Bright white for contrast */
    }

    .footer {
        margin-top: 20px;
        font-size: 14px;
        color: #777777;
    }

    .footer a {
        color: #00bfff; /* Maintain the bright blue for links */
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .footer a:hover {
        color: #008cff; /* Slightly lighter blue on hover for visual feedback */
    }

    .highlight {
        background: #333333; /* Dark background for highlighted sections */
        padding: 5px 10px;
        border-radius: 5px;
        color: #00bfff; /* Bright blue text for highlights */
        font-weight: bold;
        display: inline-block;
    }

    </style>
</head>
<body>
    <div class="receipt-container">
        <h2>Library Receipt</h2>
        <p><strong>Student Full Name:</strong> <?php echo $fullname; ?></p>
        <p><strong>Student AIUB ID:</strong> <?php echo $aiub_id; ?></p>
        <p><strong>Student Email:</strong> <?php echo $aiub_email; ?></p>
        <p><strong>Chosen Book(s):</strong> 
        <span class="highlight">
            <?php 
            if (!empty($books)) {
                echo is_array($books) ? implode(", ", $books) : htmlspecialchars($books);
            } else {
                echo "None selected";
            }
            ?>
        </span>
        </p>


        <p><strong>Borrow Date:</strong> <?php echo $borrow_date; ?></p>
        <p><strong>Return Date:</strong> <?php echo $return_date; ?></p>
        <p><strong>Token No:</strong> <?php echo $token; ?></p>
        <p><strong>Fees:</strong> <span class="highlight"><?php echo $fee; ?></span></p>
    
        <div class="footer">
            <p>Thank you for using the library system!</p>
            <p><a href="index.php">Borrow Another Book</a></p>
        </div>
    </div>
</body>
</html>
