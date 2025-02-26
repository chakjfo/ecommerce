<?php
// Ensure this is an admin-only page
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once "db_connection.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management</title>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    
    <style>
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        .user-table th, .user-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .user-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .user-table tr:hover {
            background-color: #f5f5f5;
            cursor: pointer;
        }
        .user-table tr.selected {
            background-color: #e2f0ff;
        }
        .edit-btn {
            padding: 8px 15px;
            margin-bottom: 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .edit-btn:hover {
            background-color: #45a049;
        }
        .edit-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; /* Adjusted margin-top to 5% */
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-buttons {
            margin-top: 20px;
            text-align: right;
        }
        .save-btn {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        .cancel-btn {
            padding: 8px 15px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .delete-btn {
            padding: 8px 15px;
            margin-bottom: 20px;
            background-color: rgb(255, 0, 0);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .delete-btn:hover {
            background-color: rgb(255, 0, 0);
        }
        .delete-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        /* Responsive Table */
        @media (max-width: 768px) {
            .user-table thead { display: none; }
            .user-table tbody, .user-table tr, .user-table td { display: block; width: 100%; }
            .user-table tr { margin-bottom: 15px; border: 1px solid #ddd; }
            .user-table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }
            .user-table td:before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                text-align: left;
                font-weight: bold;
            }
        }
    </style>
</head>
<body>
    <h1>Users Management</h1>
    
    <button id="editUserBtn" class="edit-btn" disabled>Edit Selected User</button>
    <button id="deleteUserBtn" class="delete-btn" disabled>Delete Selected User</button>
    
    <table id="usersTable" class="user-table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch all users from the database
            $sql = "SELECT UserID, Username, Email, PhoneNumber, Role FROM users";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr data-id='" . $row["UserID"] . "'>";
                    echo "<td>" . $row["UserID"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["Username"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["Email"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["PhoneNumber"] ?? 'Not provided') . "</td>";
                    echo "<td>" . htmlspecialchars($row["Role"]) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No users found</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit User</h2>
            <form id="editUserForm" method="post">
                <input type="hidden" id="userId" name="userId">
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phoneNumber">Phone Number:</label>
                    <input type="text" id="phoneNumber" name="phoneNumber">
                </div>
                
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role">
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="newPassword">New Password (leave blank to keep current):</label>
                    <input type="password" id="newPassword" name="newPassword">
                </div>
                
                <div class="form-buttons">
                    <button type="button" class="cancel-btn" id="cancelEdit">Cancel</button>
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#usersTable').DataTable({
                "pageLength": 10,
                "lengthChange": true,
                "searching": true,
                "ordering": true
            });
            
            let selectedUserId = null;
            
            // Handle row selection
            $('#usersTable tbody').on('click', 'tr', function() {
                $('#usersTable tbody tr').removeClass('selected');
                $(this).addClass('selected');
                selectedUserId = $(this).data('id');
                $('#editUserBtn').prop('disabled', false);
                $('#deleteUserBtn').prop('disabled', false);
            });
            
            // Edit button click handler
            $('#editUserBtn').on('click', function() {
                if (selectedUserId) {
                    // Fetch user data for the modal
                    $.ajax({
                        url: 'get_user.php',
                        type: 'GET',
                        data: { userId: selectedUserId },
                        dataType: 'json',
                        success: function(data) {
                            // Populate the form with user data
                            $('#userId').val(data.UserID);
                            $('#username').val(data.Username);
                            $('#email').val(data.Email);
                            $('#phoneNumber').val(data.PhoneNumber);
                            $('#role').val(data.Role);
                            $('#newPassword').val('');
                            
                            // Show the modal
                            $('#editUserModal').css('display', 'block');
                        },
                        error: function() {
                            alert('Error fetching user data');
                        }
                    });
                }
            });
            
            // Close the modal
            $('.close, #cancelEdit').on('click', function() {
                $('#editUserModal').css('display', 'none');
            });
            
            // Close modal if clicking outside of it
            $(window).on('click', function(event) {
                if ($(event.target).is('#editUserModal')) {
                    $('#editUserModal').css('display', 'none');
                }
            });
            
            // Handle form submission
            $('#editUserForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: 'update_user.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('User updated successfully!');
                            $('#editUserModal').css('display', 'none');
                            // Reload the page to refresh the user list
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error updating user');
                    }
                });
            });
            
            // Delete button click handler
            $('#deleteUserBtn').on('click', function() {
                if (selectedUserId && confirm("Are you sure you want to delete this user?")) {
                    $.ajax({
                        url: 'delete_user.php',
                        type: 'POST',
                        data: { userId: selectedUserId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert('User deleted successfully!');

                                // Remove the row from DataTable dynamically
                                let table = $('#usersTable').DataTable();
                                let row = $('tr[data-id="' + selectedUserId + '"]');
                                table.row(row).remove().draw();

                                // Disable buttons since no row is selected
                                $('#editUserBtn, #deleteUserBtn').prop('disabled', true);
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('Error deleting user');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>