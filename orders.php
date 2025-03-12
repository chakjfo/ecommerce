<?php
    // Ensure this is an admin-only page
    session_start();
    if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
    require_once "db_connection.php";

    // Fetch orders
    $order_query = "SELECT 
                    o.OrderID,
                    co.customer_name,
                    o.OrderDate,
                    o.TotalAmount,
                    co.payment_method
                FROM orders o
                JOIN customer_orders co ON o.OrderID = co.order_id
                ORDER BY o.OrderDate DESC";
    $order_result = $conn->query($order_query);

    // Check for success or error messages in the URL
    if (isset($_GET['success'])) {
        $_SESSION['success_message'] = urldecode($_GET['success']);
    }
    if (isset($_GET['error'])) {
        $_SESSION['error_message'] = urldecode($_GET['error']);
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Order Management</title>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" media="screen">
        
        <style>

            /* Alert styles */
            .alert {
                position: relative; /* Changed from fixed to relative */
                top: 0;
                right: 0;
                z-index: 1000;
                padding: 15px;
                border-radius: 5px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                animation: slideIn 0.5s ease-out;
                margin-bottom: 20px; /* Add margin to separate from the table */
            }

            .alert-success {
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }

            .alert-danger {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            #orderTable th:nth-child(5),
    #orderTable td:nth-child(5) {
        width: 20%; /* Adjust this value as needed */
    }

    #content .ordertable {
        flex: 1; /* Allow the content to grow and fill the remaining space */
        padding: 20px;
        display: flex;
        flex-direction: column;
    }
            @media (max-width: 768px) {
                #sidebar {
                    margin-left: -250px;
                }
                #sidebar.active {
                    margin-left: 0;
                }
                #sidebarCollapse span {
                    display: none;
                }
            }

            /* Keep existing styles, add order-specific styles */
            .order-details-modal .modal-dialog {
                max-width: 700px;
            }
            .order-items-list {
                list-style: none;
                padding: 0;
            }
            .order-item {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
                padding: 10px;
                background: #f8f9fa;
                border-radius: 4px;
            }
            .waybill-header {
                border-bottom: 2px solid #000;
                margin-bottom: 20px;
                padding-bottom: 15px;
            }

            body {
                font-family: 'Arial', sans-serif;
                background-color: #f8f9fa;
            }
            #sidebar {
                min-width: 250px;
                max-width: 250px;
                min-height: 100vh;
                background: #343a40;
                color: #fff;
                transition: all 0.3s;
            }
            #sidebar.active {
                margin-left: -250px;
            }
            #sidebar .sidebar-header {
                padding: 20px;
                background: #212529;
            }
            #sidebar ul.components {
                padding: 20px 0;
                border-bottom: 1px solid #4b545c;
            }
            #sidebar ul p {
                color: #fff;
                padding: 10px;
            }
            
            #sidebar ul li a {
                padding: 10px;
                font-size: 1.1em;
                display: block;
                color: #fff;
                text-decoration: none;
            }
            #sidebar ul li a:hover {
                color: #000;
                background: #fff;
            }
            #sidebar ul li.active > a,
            a[aria-expanded="true"] {
                color: #fff;
                background: #6d7fcc;
            }
            ul ul a {
                font-size: 0.9em !important;
                padding-left: 30px !important;
                background: #4b545c;
            }

            .wrapper {
                display: flex;
                width: 100%;
            }
            #content {
                width: 100%;
                padding: 20px;
                min-height: 100vh;
                transition: all 0.3s;
            }

            .card {
        flex: 1; /* Allow the card to grow and fill the remaining space */
        display: flex;
        flex-direction: column;
    }

    .container {
        flex: 1; /* Allow the container to grow and fill the remaining space */
        display: flex;
        flex-direction: column;
    }

    .card-body {
        flex: 1; /* Allow the card body to grow and fill the remaining space */
        display: flex;
        flex-direction: column;
    }

    .table-responsive {
        flex: 1; /* Allow the table to grow and fill the remaining space */
        overflow-x: auto; /* Ensure horizontal scrolling for small screens */
    }
            .orderTable {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                background-color: white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                border-radius: 5px;
            }
            .orderTable th, .orderTable td {
                padding: 12px 15px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            .orderTable th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            .orderTable tr:hover {
                background-color: #f5f5f5;
                cursor: pointer;
            }
            .orderTable tr.selected {
                background-color: #e2f0ff;
            }

            #orderTable th:nth-child(5),
    #orderTable td:nth-child(5) {
        width: 23%
        ; /* Adjust this value as needed */
    }

            @media (max-width: 768px) {
                #sidebar {
                    margin-left: -250px;
                }
                #sidebar.active {
                    margin-left: 0;
                }
                #sidebarCollapse span {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <!-- Updated Sidebar -->
            <nav id="sidebar">
                <div class="sidebar-header">
                    <h3>Admin Panel</h3>
                </div>

                <ul class="list-unstyled components">
                    <li>
                        <a href="admin.php">Home</a>
                    </li>
                    <li>
                        <a href="#productSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Products</a>
                        <ul class="collapse list-unstyled" id="productSubmenu">
                            <li>
                                <a href="products.php">View All Products</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#orderSubmenu" data-bs-toggle="collapse" aria-expanded="true" class="dropdown-toggle">Orders</a>
                        <ul class="collapse show list-unstyled" id="orderSubmenu">
                            <li class="active">
                                <a href="orders.php">View All Orders</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#userSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Users</a>
                        <ul class="collapse list-unstyled" id="userSubmenu">
                            <li>
                                <a href="users.php">View All Users</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="logout.php">Logout</a>
                    </li>
                </ul>
            </nav>

            <!-- Page Content -->
            <div id="content">
                <button type="button" id="sidebarCollapse" class="btn btn-dark">
                    <i class="fas fa-align-left"></i>
                    <span></span>
                </button>
                <div class="container mt-5">
                    <!-- Display success or error message if any -->
                    <?php if(isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" id="success_message" role="alert">
                            <?php 
                                echo $_SESSION['success_message'];
                                unset($_SESSION['success_message']); // Clear message after displaying
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php 
                                echo $_SESSION['error_message'];
                                unset($_SESSION['error_message']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

    <!-- Order Management Table -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Order Management</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="orderTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($order_result->num_rows > 0): ?>
                            <?php while($order = $order_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $order['OrderID']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['OrderDate'])); ?></td>
                                    <td>$<?php echo number_format($order['TotalAmount'], 2); ?></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-info btn-sm view-order" 
                                                data-id="<?php echo $order['OrderID']; ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#orderDetailsModal">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <div class="btn-group">
                                            <button class="btn btn-warning btn-sm dropdown-toggle" 
                                                    type="button" 
                                                    data-bs-toggle="dropdown" 
                                                    aria-expanded="false">
                                                <i class="fas fa-edit"></i> Edit Status
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" data-status="pending" data-id="<?php echo $order['OrderID']; ?>">Pending</a></li>
                                                <li><a class="dropdown-item" href="#" data-status="shipped" data-id="<?php echo $order['OrderID']; ?>">Shipped</a></li>
                                                <li><a class="dropdown-item" href="#" data-status="delivered" data-id="<?php echo $order['OrderID']; ?>">Delivered</a></li>
                                                <li><a class="dropdown-item" href="#" data-status="cancelled" data-id="<?php echo $order['OrderID']; ?>">Cancelled</a></li>
                                                
                                            </ul>
                                        </div>
                                        <button class="btn btn-danger btn-sm delete-order" 
                                                data-id="<?php echo $order['OrderID']; ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

                    <!-- Order Details Modal -->
                    <div class="modal fade order-details-modal" id="orderDetailsModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header waybill-header">
                                    <h5 class="modal-title">Order #<span id="orderId"></span></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Order Date:</strong> <span id="orderDate"></span>
                                        </div>
                                        <div class="col-6">
                                            <strong>Customer:</strong> <span id="customer_name"></span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Delivery Status:</strong> <span id="deliveryStatus"></span>
                                        </div>
                                        <div class="col-6">
                                            <strong>Estimated Delivery:</strong> <span id="deliveryDate"></span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Shipping Address:</strong> 
                                        <div id="shippingAddress"></div>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Payment Method:</strong> 
                                        <span id="paymentMethod"></span>
                                    </div>
                                    <h6>Order Items:</h6>
                                    <ul class="order-items-list" id="orderItems"></ul>
                                    <div class="text-end">
                                        <h5>Subtotal: <span id="subtotal"></span></h5>
                                        <h5>Tax (8%): <span id="tax"></span></h5>
                                        <h5>Shipping: <span id="shipping"></span></h5>
                                        <h5>Total: <span id="orderTotal"></span></h5>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- JavaScript -->
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
                    <script>
                        $(document).ready(function() {
                            // Handle status update
                            $('.dropdown-item').click(function(e) {
                                e.preventDefault();
                                const orderId = $(this).data('id');
                                const newStatus = $(this).data('status');

                                $.ajax({
                                    url: 'update_order_status.php',
                                    method: 'POST',
                                    data: { order_id: orderId, status: newStatus },
                                    dataType: 'json',
                                    success: function(response) {
                                        if (response.success) {
                                            alert("Status updated successfully!");
                                            location.reload();  // Refresh the page to see the updated status
                                        } else {
                                            alert("Failed to update status: " + response.message);
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        console.error("AJAX Error:", status, error);
                                        alert("An error occurred while updating the status.");
                                    }
                                });
                            });

                            // Handle delete order
                            $('.delete-order').click(function() {
                                const orderId = $(this).data('id');

                                if (confirm('Are you sure you want to delete this order?')) {
                                    $.ajax({
                                        url: 'delete_order.php',
                                        method: 'POST',
                                        data: { order_id: orderId },
                                        dataType: 'json',
                                        success: function(response) {
                                            if (response.success) {
                                                alert('Order deleted successfully!');
                                                location.reload();  // Refresh the page to see the updated list of orders
                                            } else {
                                                alert('Failed to delete order: ' + response.message);
                                            }
                                        },
                                        error: function(xhr, status, error) {
                                            console.error('AJAX Error:', error);
                                            alert('Failed to delete order. Please try again.');
                                        }
                                    });
                                }
                            });
                        });

                        // Toggle sidebar
                        document.getElementById('sidebarCollapse').addEventListener('click', function() {
                            document.getElementById('sidebar').classList.toggle('active');
                        });

                        // Initialize DataTable
                        $('#orderTable').DataTable({
                            "order": [[0, "desc"]],
                            "columns": [
                                { "width": "10%" },
                                null,
                                { "width": "15%" },
                                { "width": "15%" },
                                { "width": "20%" } // Adjusted width for the action column
                            ]
                        });

                        // Handle view order details
                        $('.view-order').click(function() {
                            const orderId = $(this).data('id');
                            $.ajax({
                                url: 'get_order_details.php',
                                method: 'POST',
                                data: { order_id: orderId },
                                dataType: 'json',
                                success: function(response) {
                                    console.log('AJAX Response:', response);
                                    if (!response.order || !response.items) {
                                        console.error('Invalid response structure');
                                        return;
                                    }

                                    // Update modal content
                                    $('#orderId').text(response.order.OrderID);
                                    $('#customer_name').text(response.order.customer_name);
                                    $('#orderDate').text(new Date(response.order.OrderDate).toLocaleDateString('en-US', {
                                        month: 'short',
                                        day: 'numeric',
                                        year: 'numeric'
                                    }));

                                    $('#shippingAddress').text(response.order.address);
                                    $('#paymentMethod').text(response.order.payment_method);
                                    $('#deliveryStatus').text(response.order.delivery_status);
                                    $('#deliveryDate').text(response.order.delivery_date);

                                    let subtotal = 0;
                                    $('#orderItems').empty();
                                    response.items.forEach(item => {
                                        const itemTotal = item.quantity * parseFloat(item.Price);
                                        subtotal += itemTotal;

                                        $('#orderItems').append(
                                            `<li class="order-item">
                                                <div>${item.ProductName} (ID: ${item.product_id})</div>
                                                <div>
                                                    Qty: ${item.quantity} × 
                                                    $${parseFloat(item.Price).toFixed(2)} = 
                                                    $${itemTotal.toFixed(2)}
                                                </div>
                                            </li>`
                                        );
                                    });

                                    const taxRate = 0.08; // 8% tax
                                    const tax = subtotal * taxRate;
                                    const shipping = 0.00; // Free shipping
                                    const total = subtotal + tax + shipping;

                                    $('#subtotal').text('$' + subtotal.toFixed(2));
                                    $('#tax').text('$' + tax.toFixed(2));
                                    $('#shipping').text('$' + shipping.toFixed(2));
                                    $('#orderTotal').text('$' + total.toFixed(2));
                                },
                                error: function(xhr, status, error) {
                                    console.error('AJAX Error:', error);
                                    alert('Failed to fetch order details. Please try again.');
                                }
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </body>
    </html>