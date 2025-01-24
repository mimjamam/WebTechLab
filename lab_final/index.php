<?php
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'app_users');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
 
// Fetch all books from the database for display
$books_query = "SELECT * FROM books";
$books_result = mysqli_query($conn, $books_query);
 
// Define maximum return date (you may want to calculate it dynamically)
$maxReturnDate = date('Y-m-d', strtotime('+30 days'));  // Max return date is 30 days from today
 
// Handle the book addition logic
if (isset($_POST['add_book'])) {
    $new_author_name = mysqli_real_escape_string($conn, $_POST['new_author_name']);
    $new_book_title = mysqli_real_escape_string($conn, $_POST['new_book_title']);
    $new_isbn = mysqli_real_escape_string($conn, $_POST['new_isbn']);
    $new_book_quantity = mysqli_real_escape_string($conn, $_POST['new_book_quantity']);

    // Ensure all fields are filled
    if (!empty($new_author_name) && !empty($new_book_title) && !empty($new_isbn) && !empty($new_book_quantity)) {
        $add_book_query = "INSERT INTO books (author_name, title, isbn, quantity) VALUES ('$new_author_name', '$new_book_title', '$new_isbn', '$new_book_quantity')";

        // Execute the query and check for errors
        if (mysqli_query($conn, $add_book_query)) {
            // Book added successfully, reload the page to display the new book
            header("Location: index.php");
            exit();
        } else {
            echo "<p>Error adding book: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p>All fields are required!</p>";
    }
}

// Handle book search logic
$searched_book = null;
if (isset($_POST['search_book'])) {
    $search_book_title = mysqli_real_escape_string($conn, $_POST['search_book_title']);
    $search_query = "SELECT * FROM books WHERE title = '$search_book_title'";
    $search_result = mysqli_query($conn, $search_query);
    if (mysqli_num_rows($search_result) > 0) {
        $searched_book = mysqli_fetch_assoc($search_result);
    } else {
        // echo "<p>Book not found.</p>";
        echo "<script>
                    alert('Book not found!');
                    window.location.href = 'index.php';
                  </script>";
    }
}

// Handle book update logic
if (isset($_POST['update_book'])) {
    $current_book_title = mysqli_real_escape_string($conn, $_POST['current_book_title']);
    $new_book_title = mysqli_real_escape_string($conn, $_POST['new_book_title']);
    $new_book_quantity = mysqli_real_escape_string($conn, $_POST['new_book_quantity']);

    // Prepare the SQL query to update the book title and quantity
    $update_query = "UPDATE books SET ";
    $update_fields = [];

    if (!empty($new_book_title)) {
        $update_fields[] = "title = '$new_book_title'";
    }
    if (!empty($new_book_quantity)) {
        $update_fields[] = "quantity = '$new_book_quantity'";
    }

    if (!empty($update_fields)) {
        $update_query .= implode(", ", $update_fields) . " WHERE title = '$current_book_title'";

        // Execute the query and handle the result
        if (mysqli_query($conn, $update_query)) {
            // Display success alert and redirect
            echo "<script>
                    alert('Book information updated successfully!');
                    window.location.href = 'index.php';
                  </script>";
            exit();
        } else {
            echo "<p>Error updating book: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p>No fields to update!</p>";
    }
}

// Handle book delete logic
if (isset($_POST['delete_book'])) {
    $delete_book_isbn = mysqli_real_escape_string($conn, $_POST['delete_book_isbn']);

    // Validate ISBN format (assuming ISBN-10 or ISBN-13)
    if (preg_match('/^\d{10}(\d{3})?$/', $delete_book_isbn)) {
        // Delete the book from the database
        $delete_query = "DELETE FROM books WHERE isbn = '$delete_book_isbn'";
        if (mysqli_query($conn, $delete_query)) {
            // Redirect to the same page to refresh the book list
            header("Location: " . $_SERVER['PHP_SELF']);
            exit(); // Ensure no further code is executed
        } else {
            echo "<p>Error deleting book: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<script>
                alert('Invalid ISBN format. Please enter a valid ISBN.');
                window.location.href = 'index.php';
              </script>";
    }
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
    
    <title>Borrow Your Book</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <h2>Book Borrowing Management System </h2><br>
    <div class="id">
        <img src="my_id.jpg" >
    </div><br>
    
    <div class="outside">
        <div class="out1">
            <!-- Selected token -->
            <h3>Selected Tokens:</h3>
<?php
    // Path to the usedtoken.json file
    $jsonFilePath = 'usedtoken.json';

    // Check if the JSON file exists
    if (file_exists($jsonFilePath)) {
        // Read the file content
        $jsonContent = file_get_contents($jsonFilePath);

        // Decode JSON data into an associative array
        $tokens = json_decode($jsonContent, true);

        // Check if the file contains valid data
        if (!empty($tokens)) {
            echo "<ul>";
            foreach ($tokens as $token) {
                echo "<li>" . htmlspecialchars($token) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No tokens found in the file.</p>";
        }
    } else {
        echo "<p>Token file not found.</p>";
    }
?>

        </div>
        <div class="middle">
            <div class="first">
                <div class="box1">
                <!-- DISPLAY ALL BOOKS IN A TABLE -->
<h2>All Books:</h2>
<?php
// Check if there are any books
if (mysqli_num_rows($books_result) > 0) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr>
            
            <th>Title</th>
            <th>Author Name</th>
            <th>ISBN</th>
            <th>Quantity</th>
          </tr>";

    // Fetch and display all books
    while ($book = mysqli_fetch_assoc($books_result)) {
        echo "<tr>
                
                <td>" . htmlspecialchars($book['title']) . "</td>
                <td>" . htmlspecialchars($book['author_name']) . "</td>
                <td>" . htmlspecialchars($book['isbn']) . "</td>
                <td>" . htmlspecialchars($book['quantity']) . "</td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "<p>No books available.</p>";
}
?>

                </div>
                <div class="box1">
                
                <h3>Update Book Information</h3>
<form action="" method="POST">
    <table>
        <tr>
            <td><label for="search_book_title">Search Book Title:</label></td>
            <td><input type="text" id="search_book_title" name="search_book_title" required></td>
            <td><input type="submit" name="search_book" value="Search Book"></td>
        </tr>
    </table>
</form>

<?php if ($searched_book): ?>
<form action="" method="POST">
    <table>
        <tr>
            <td><label for="current_book_title">Current Book Title:</label></td>
            <td><input type="text" id="current_book_title" name="current_book_title" value="<?php echo htmlspecialchars($searched_book['title']); ?>" readonly></td>
        </tr>
        <tr>
            <td><label for="new_book_title">New Book Title:</label></td>
            <td><input type="text" id="new_book_title" name="new_book_title"></td>
        </tr>
        <tr>
            <td><label for="new_book_quantity">New Book Quantity:</label></td>
            <td><input type="number" id="new_book_quantity" name="new_book_quantity"></td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="submit" name="update_book" value="Update Book">
            </td>
        </tr>
    </table>
</form>
<?php endif; ?>


                </div>
    
            </div>
            <div class="second" >
                <div class="box2">
                    <img src="i1.jpeg" style="width:150px ;height: 150px;">
                </div>
                <div class="box2">
                    <img src="i2.jpeg" style="width:150px ;height: 150px;">
                </div>
                <div class="box2">
                    <img src="i3.jpeg " style="width:150px ;height: 150px;">
                </div>
            </div>
            <div class="third">
                <div class="box3">
                <!-- ADD NEW BOOK -->
  <!-- Add New Book Form -->
  <div style="display: flex; gap: 10px; align-items: flex-start;">
    <!-- Add New Book Form -->
    <form action="" method="POST" style="border: 1px solid #ccc; padding: 10px; border-radius: 5px; flex: 1;">
        <h4 style="margin: 0 0 10px;">Add New Book</h4>
        <label for="new_author_name" style="display: block; margin-bottom: 5px;">Author Name:</label>
        <input type="text" id="new_author_name" name="new_author_name" required 
               style="display: block; margin-bottom: 10px; width: 100%; padding: 5px; font-size: 14px;">
        <label for="new_book_title" style="display: block; margin-bottom: 5px;">Title:</label>
        <input type="text" id="new_book_title" name="new_book_title" required 
               style="display: block; margin-bottom: 10px; width: 100%; padding: 5px; font-size: 14px;">
        <label for="new_isbn" style="display: block; margin-bottom: 5px;">ISBN:</label>
        <input type="text" id="new_isbn" name="new_isbn" required 
               style="display: block; margin-bottom: 10px; width: 100%; padding: 5px; font-size: 14px;">
        <label for="new_book_quantity" style="display: block; margin-bottom: 5px;">Quantity:</label>
        <input type="number" id="new_book_quantity" name="new_book_quantity" required 
               style="display: block; margin-bottom: 10px; width: 100%; padding: 5px; font-size: 14px;">
        <input type="submit" name="add_book" value="Add Book" 
               style="display: block; width: 100%; padding: 5px; font-size: 14px;">
    </form>

    <!-- Delete Book Form -->
    <form action="" method="POST" style="border: 1px solid #ccc; padding: 10px; border-radius: 5px; flex: 1;">
        <h4 style="margin: 0 0 10px;">Delete Book</h4>
        <label for="delete_book_isbn" style="display: block; margin-bottom: 5px;">ISBN:</label>
        <input type="text" id="delete_book_isbn" name="delete_book_isbn" required 
               style="display: block; margin-bottom: 10px; width: 100%; padding: 5px; font-size: 14px;">
        <input type="submit" name="delete_book" value="Delete Book" 
               style="display: block; width: 100%; padding: 5px; font-size: 14px;">
    </form>
</div>



                </div>
            </div>
            <div class="fourth">
            <div class="box4">
            <form action="process.php" method="post">
            <label for="fullname">Student Full Name: </label><br>
            <input type="text" name="fullname"><br>
            <label for="aiub_id">Student AIUB ID : </label><br>
            <input type="text" name="aiub_id"><br>
            <label for="aiub_email">Student Email : </label><br>
            <input type="text" name="aiub_email"><br>
            <label for="books">Choose a Book :</label>

            <select name="books[]" id="books" style="width: 300px" multiple>
            <option value="Machine Learning for Absolute Beginners">Machine Learning for Absolute Beginners</option>
            <option value="The Hundred-Page Machine Learning Book">The Hundred-Page Machine Learning Book</option>
            <option value="Machine Learning for Dummies">Machine Learning for Dummies</option>
            <option value="Introduction to Machine Learning with Python">Introduction to Machine Learning with Python</option>
            <option value="Hands-On Machine Learning with Scikit-Learn, Keras, and TensorFlow">Hands-On Machine Learning with Scikit-Learn, Keras, and TensorFlow</option>
            <option value="Machine Learning for Hackers">Machine Learning for Hackers</option>
            <option value="Machine Learning in Action">Machine Learning in Action</option>
            <option value="Data Mining: Practical Machine Learning Tools and Techniques">Data Mining: Practical Machine Learning Tools and Techniques</option>
            <option value="Reinforcement Learning">Reinforcement Learning</option>
            <option value="Causal Inference in Statistics: A Primer">Causal Inference in Statistics: A Primer</option>
            </select>


            <label for="borrow_date">Borrow date:</label><br>
            <input type="date" id="borrow_date" name="borrow_date" value="<?php echo date('Y-m-d'); ?>" /><br>


            <label for="token">Token No: </label><br>

            <input type="number" id="token" name="token" /><br>

            <label for="return_date">Return Date:</label><br>
                        <input type="date" id="return_date" name="return_date" 
                            min="<?php echo date('Y-m-d'); ?>" 
                            max="<?php echo $maxReturnDate; ?>" /><br>


            <label for="fee">Fees </label><br>

            <input type="number" id="fee" name="fee" /><br>

            <!-- <input type="submit" name="submit" value="submit"> -->
            <tr>
            <td></td>
            <td>
            <input type="submit" name="submit" value="submit">
            </td>
        </tr>
            </form>



                </div>
                <!-- Available Token  -->
                <div class="box5"> <h1>Available Tokens:</h1>
                    <ul>
                        <?php
                        // Load and display tokens from token.json
                        $tokenFile = 'token.json';
                        if (file_exists($tokenFile)) {
                            $jsonContent = file_get_contents($tokenFile);
                            $data = json_decode($jsonContent, true);
                            if (!empty($data['token'])) {
                                foreach ($data['token'] as $token) {
                                    echo '<li>' . htmlspecialchars($token) . '</li>';
                                }
                            } else {
                                echo '<li><b>No tokens available.</b></li>';
                            }
                        } else {
                            echo '<li>Token file not found.</li>';
                        }
                        ?>
                    </ul></div>
            </div>
        </div>
        <div class="out2">
    <h2>System Overview</h2>
    <p>Welcome to the Book Borrowing Management System!</p>
    <p>This system allows you to borrow and return books with ease.</p>

    <h3>Important Instructions:</h3>
    <ul>
        <li>Fill in your details correctly before submitting the form.</li>
        <li>Don't forget to select a return date and enter any fees.</li>
        <li>Make sure to choose a valid token for your request.</li>
    </ul>
</div>

</div>  
</body>
</html>