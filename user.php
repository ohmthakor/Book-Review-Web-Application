<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header">
        <h1>My Profile</h1>
        <div class="mainpage-button">
            <a href="index.php"><button>Back to Main Page</button></a>
        </div>
        <div class="logout-button">
            <form action="logout.php" method="post">
                <button type="submit">Log Out</button>
            </form>
        </div>
    </div>
    <div class="container">
        <div class="main-content">
            <h2>My Reading List</h2>
            <div class="reading-list-create">
                <form id="create-reading-list-form">
                    <input type="text" name="list_name" id="list_name" placeholder="Enter reading list name" required>
                    <button type="submit">Create Reading List</button>
                </form>
            </div>
            <div>
                <label for="reading-list-select">Select Reading List:</label>
                <select id="reading-list-select" onchange="fetchReadingList(<?php echo $_SESSION['user_id']; ?>)">
                    <!-- Options will be populated by JavaScript -->
                </select>
                <button onclick="deleteSelectedReadingList()">Delete Reading List</button>
            </div>
            <div>
                <label for="sort-by-select">Sort By:</label>
                <select id="sort-by-select" onchange="fetchReadingList(<?php echo $_SESSION['user_id']; ?>)">
                    <option value="status">Status</option>
                    <option value="genre">Genre</option>
                    <option value="title">Title</option>
                    <option value="author">Author</option>
                </select>
            </div>
            <div id="reading-list-details"></div>

            <h2>My Reviews</h2>
            <ul id="user-reviews-list"></ul>
        </div>
    </div>
    <script>
        async function fetchReadingLists(userId) {
            try {
                const response = await fetch(`server.php?action=getUserReadingLists&user_id=${userId}`);
                const readingLists = await response.json();
                const readingListSelect = document.getElementById("reading-list-select");
                readingListSelect.innerHTML = "<option value=''>Select a reading list</option>";
                readingLists.forEach((list) => {
                    const option = document.createElement("option");
                    option.value = list.list_name;
                    option.textContent = list.list_name;
                    readingListSelect.appendChild(option);
                });
            } catch (err) {
                console.error("Error fetching reading lists:", err);
            }
        }

        async function fetchReadingList(userId) {
            const listName = document.getElementById("reading-list-select").value;
            const sortBy = document.getElementById("sort-by-select").value;
            if (!listName) return;
            try {
                const response = await fetch(`server.php?action=getReadingList&user_id=${userId}&list_name=${listName}&sort_by=${sortBy}`);
                const readingList = await response.json();
                const readingListDetails = document.getElementById("reading-list-details");
                readingListDetails.innerHTML = "";

                readingList.forEach((item) => {
                    const div = document.createElement("div");
                    div.className = "reading-list-box";
                    div.innerHTML = `
                        <p>Book: ${item.title}</p>
                        <p>Author: ${item.fname} ${item.lname}</p>
                        <p>Genre: ${item.genre}</p>
                        <p>Status: ${item.status}</p>
                        <p>Date Added: ${item.date_added}</p>
                        <button onclick="removeFromReadingList(${item.list_id})">Remove</button>
                    `;
                    readingListDetails.appendChild(div);
                });
            } catch (err) {
                console.error("Error fetching reading list:", err);
            }
        }

        async function createReadingList(userId, listName) {
            try {
                const formData = new FormData();
                formData.append("action", "createReadingList");
                formData.append("user_id", userId);
                formData.append("list_name", listName);

                const response = await fetch("server.php", {
                    method: "POST",
                    body: formData,
                });
                const result = await response.json();
                if (result.message) {
                    alert(result.message);
                } else {
                    alert(result.error);
                }
            } catch (err) {
                console.error("Error creating reading list:", err);
            }
        }

        async function deleteReadingList(listId) {
            try {
                const response = await fetch(`server.php?action=deleteReadingList&list_id=${listId}`, { method: "GET" });
                const result = await response.json();
                if (result.message) {
                    alert(result.message);
                    fetchReadingList(<?php echo $_SESSION['user_id']; ?>);
                } else {
                    alert(result.error);
                }
            } catch (err) {
                console.error("Error deleting reading list:", err);
            }
        }

        async function fetchUserReviews(userId) {
            try {
                const response = await fetch(`server.php?action=getUserReviews&user_id=${userId}`);
                const reviews = await response.json();
                const userReviewsList = document.getElementById("user-reviews-list");
                userReviewsList.innerHTML = "";
                reviews.forEach((review) => {
                    const li = document.createElement("li");
                    li.innerHTML = `
                        Book: ${review.title} <br>
                        Rating: ${review.rating} <br>
                        Review: ${review.review_text}
                        <button onclick="deleteReview(${review.review_id})">Delete</button>
                        <button onclick="editReviewPrompt(${review.review_id}, ${review.rating}, '${review.review_text}')">Edit</button>
                    `;
                    userReviewsList.appendChild(li);
                });
            } catch (err) {
                console.error("Error fetching user reviews:", err);
            }
        }

        async function deleteReview(reviewId) {
            const userId = <?php echo $_SESSION['user_id']; ?>;
            const response = await fetch(`server.php?action=deleteReview&review_id=${reviewId}&user_id=${userId}`, { method: "GET" });
            const result = await response.json();
            if (result.message) {
                alert(result.message);
                fetchUserReviews(userId);
            } else {
                alert(result.error);
            }
        }

        function editReviewPrompt(reviewId, currentRating, currentText) {
            const newRating = prompt("Enter new rating (1-5):", currentRating);
            const newText = prompt("Enter new review text:", currentText);
            if (newRating && newText) {
                editReview(reviewId, newRating, newText);
            }
        }

        async function editReview(reviewId, newRating, newText) {
            const userId = <?php echo $_SESSION['user_id']; ?>;
            const data = { review_id: reviewId, rating: newRating, review_text: newText, user_id: userId };
            const response = await fetch("server.php?action=editReview", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data),
            });
            const result = await response.json();
            if (result.message) {
                alert(result.message);
                fetchUserReviews(userId);
            } else {
                alert(result.error);
            }
        }

        async function removeFromReadingList(listId) {
            try {
                const response = await fetch(`server.php?action=removeFromReadingList&list_id=${listId}`, { method: "GET" });
                const result = await response.json();
                if (result.message) {
                    alert(result.message);
                    fetchReadingList(<?php echo $_SESSION['user_id']; ?>);
                } else {
                    alert(result.error);
                }
            } catch (err) {
                console.error("Error removing book from reading list:", err);
            }
        }

        async function fetchReviews(bookId) {
            try {
                const response = await fetch(`server.php?action=getReviews&book_id=${bookId}`);
                const reviews = await response.json();
                const reviewsList = document.getElementById("reviews-list");
                if (reviewsList) {
                    reviewsList.innerHTML = "";
                    reviews.forEach((review) => {
                        const li = document.createElement("li");
                        li.innerHTML = `
                            User: ${review.username}, Rating: ${review.rating} <br>
                            Review: ${review.review_text}
                            ${review.user_id == userId || userRole == 'admin' ? `
                            <button onclick="deleteReview(${review.review_id})">Delete</button>
                            <button onclick="editReviewPrompt(${review.review_id}, ${review.rating}, '${review.review_text}')">Edit</button>
                            ` : ''}
                        `;
                        reviewsList.appendChild(li);
                    });
                }
            } catch (err) {
                console.error("Error fetching reviews:", err);
            }
        }

        async function deleteSelectedReadingList() {
            const listName = document.getElementById("reading-list-select").value;
            if (!listName) {
                alert("Please select a reading list to delete.");
                return;
            }
            const userId = <?php echo $_SESSION['user_id']; ?>;
            try {
                const response = await fetch(`server.php?action=deleteReadingList&user_id=${userId}&list_name=${listName}`, { method: "GET" });
                const result = await response.json();
                if (result.message) {
                    alert(result.message);
                    fetchReadingLists(userId);
                    document.getElementById("reading-list-details").innerHTML = "";
                } else {
                    alert(result.error);
                }
            } catch (err) {
                console.error("Error deleting reading list:", err);
            }
        }

        // Load reading lists and reviews on page load
        const userId = <?php echo $_SESSION['user_id']; ?>;
        fetchReadingLists(userId);
        fetchUserReviews(userId);

        document.getElementById('create-reading-list-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const listName = document.getElementById('list_name').value.trim();
            if (listName) {
                await createReadingList(userId, listName);
                fetchReadingLists(userId);
                document.getElementById('list_name').value = '';
            }
        });
    </script>
</body>
</html>
