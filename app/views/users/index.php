<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">User Management</h1>

        <!-- Add User Button -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#userModal"
            onclick="openModal()">Add User</button>

        <a href="/user/chart" class="btn btn-primary mb-3">View Chart</a>

        <!-- User Table -->
        <table id="userTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Phone Number</th>
                    <th>Email</th>
                    <th>Image</th>
                </tr>
            </thead>
            <tbody id="userTable">

            </tbody>
        </table>
    </div>

    <!-- User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm" enctype="multipart/form-data">
                        <input type="hidden" id="userId">
                        <div class="mb-3">
                            <label>Full Name</label>
                            <input type="text" id="fullName" name="fullName" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Phone Number</label>
                            <input type="text" id="phoneNumber" name="phoneNumber" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" id="email" name="email" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Image</label>
                            <input type="file" id="image" name="image[]" multiple class="form-control">
                        </div>
                        <button type="button" class="btn btn-primary" onclick="saveUser()">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script>
        function openModal() {
            $('#userForm')[0].reset();
            $('#userId').val('');
        }

        function saveUser() {
            const formData = new FormData($('#userForm')[0]);
            const userId = $('#userId').val();
            const url = userId ? `/user/update/${userId}` : '/user/create';

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    const res = JSON.parse(response);

                    if (res.status === 'error') {
                        displayErrors(res.errors); // Show validation errors
                    } else {
                        $('#userTable').DataTable().ajax.reload();
                        $('#userModal').modal('hide');
                    }
                },
                error: function () {
                    alert('Failed to save user.');
                }
            });
        }

        function displayErrors(errors) {
            $('.error').remove(); // Clear previous errors

            if (errors.fullName) {
                $('#fullName').after(`<div class="text-danger error">${errors.fullName}</div>`);
            }
            if (errors.phoneNumber) {
                $('#phoneNumber').after(`<div class="text-danger error">${errors.phoneNumber}</div>`);
            }
            if (errors.email) {
                $('#email').after(`<div class="text-danger error">${errors.email}</div>`);
            }
        }

        $(function () {
            $('#userTable').DataTable({
                "processing": true,
                "serverSide": true,  // Enable server-side processing
                "ajax": {
                    "url": "/user/list-users",  // This URL will fetch the user data
                    "type": "GET"
                },
                "columns": [
                    { "data": "id" },
                    { "data": "fullName" },
                    { "data": "phoneNumber" },
                    { "data": "email" },
                    {
                        "data": "image", "render": function (data) {
                            let images = '';
                            $.each(data, function (index, value) {
                                images += `<img src="${value}" alt="Image" width="50" height="50" style="margin-right: 5px;">`;
                            });
                            return images;
                        }
                    }
                ]
            });
        })
    </script>
</body>

</html>