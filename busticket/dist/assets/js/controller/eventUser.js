$(document.body).on("submit", "#userprofile", function (e) {

    e.preventDefault();

    var username = $("#username").val();

    var obj = new Object();
    obj.username = username;


    $.ajax({
        type: "PUT",
        url: 'assets/api/profile',
        contentType: 'application/json',
        data: JSON.stringify(obj),
        dataType: "json",
        success: function (data) {

            if (data.insertStatus) {

                alert("Update successful");

                window.location.href = "#profile";

            }
            else {
                alert("Update fail " + data.errorMessage)
            }
        },
        error: function (xhr, statusText, err) {

            if (xhr.status == 401) {
                //response text from the server if there is any
                var responseText = JSON.parse(xhr.responseText);
                bootbox.alert("Error 401 - Unauthorized: " + responseText.message);

                $("#loginname").html(data.username);
                sessionStorage.removeItem("token");
                sessionStorage.removeItem("login");
                window.location.href = "#login";
                return;
            }

            if (xhr.status == 404) {
                bootbox.alert("Error 404 - API resource not found at the server");
            }

        }
    });
});
