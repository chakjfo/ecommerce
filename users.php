<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require 'db_connection.php';

$query = "SELECT UserID, Username, Email, PhoneNumber, Role FROM users";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
        }
        .container {
            width: calc(100% - 200px);
            margin-left: 200px;
            padding: 20px;
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: left;
            word-wrap: break-word;
        }
        th {
            background: #333;
            color: white;
        }
        td[contenteditable="true"] {
            background: #f9f9f9;
            cursor: pointer;
        }
        .edit-btn, .save-btn {
            padding: 5px 10px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }
        .edit-btn {
            background-color: #007bff;
            color: white;
        }
        .save-btn {
            background-color: #28a745;
            color: white;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Users</h2>
        <table id="usersTable" class="display">
            <thead>
                <tr>
                    <th>UserID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr data-id="<?= $row['UserID'] ?>">
                        <td><?= htmlspecialchars($row['UserID']) ?></td>
                        <td contenteditable="false" data-column="Username"><?= htmlspecialchars($row['Username']) ?></td>
                        <td contenteditable="false" data-column="Email"><?= htmlspecialchars($row['Email']) ?></td>
                        <td contenteditable="false" data-column="PhoneNumber"><?= htmlspecialchars($row['PhoneNumber']) ?></td>
                        <td contenteditable="false" data-column="Role"><?= htmlspecialchars($row['Role']) ?></td>
                        <td>
                            <button class="edit-btn">Edit</button>
                            <button class="save-btn">Save</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function () {
            $('#usersTable').DataTable({
                "autoWidth": false,
                "scrollX": true
            });

            $(document).on('click', '.edit-btn', function () {
                let row = $(this).closest('tr');
                row.find('td[contenteditable="false"]').attr('contenteditable', 'true').css('background', '#f9f9f9');
                row.find('.edit-btn').hide();
                row.find('.save-btn').show();
            });

            $(document).on('click', '.save-btn', function () {
                let row = $(this).closest('tr');
                let userID = row.data('id');
                let updatedData = {};

                row.find('td[contenteditable="true"]').each(function () {
                    let column = $(this).data('column');
                    let value = $(this).text();
                    updatedData[column] = value;
                });

                $.ajax({
                    url: 'update_user.php',
                    method: 'POST',
                    data: { id: userID, updates: updatedData },
                    success: function (response) {
                        console.log(response);
                        row.find('td[contenteditable="true"]').attr('contenteditable', 'false').css('background', 'white');
                        row.find('.edit-btn').show();
                        row.find('.save-btn').hide();
                    },
                    error: function () {
                        alert('Error updating user.');
                    }
                });
            });
        });
    </script>
</body>
</html>
