$(function () {

    $.ajaxSetup({
        beforeSend: function (xhr) {
            var token = sessionStorage.getItem("token");
            xhr.setRequestHeader("Authorization", "Bearer " + token);
        }
    });
    function parseHash(newHash, oldHash) {
        crossroads.parse(newHash);
    }
    crossroads.ignoreState = true;

    var routeuserprofile = crossroads.addRoute('/profile', function () {
        if (!sessionStorage.token) {
            window.location.href = "#login";
            return;
        }
        $.ajax({
            type: "GET",
            url: 'assets/api/profile',
            dataType: "json",
            success: function (data) {
                console.log(data);
                $.get('assets/js/templates/user-profile.handlebars').then(function (src) {
                    var template = Handlebars.compile(src);
                    var html = template(data);

                    $("#divcontent").empty();
                    $("#divcontent").html(html);

                    $(".page-title").empty();
                    $(".page-title").append("Profile");

                    $(".breadcrumb").empty();
                    $(".breadcrumb").append("<li class='breadcrumb-item'><a href='#profile'>Home</a></li>");
                    $(".breadcrumb").append("<li class='breadcrumb-item active'>Profile</li>");

                })
            },
            error: function (xhr, statusText, err) {
                //console.log("hello error");
                console.log(xhr);
                console.log(statusText);
                console.log(err);
                if (xhr.status == 401) {
                    //response text from the server if there is any
                    var responseText = JSON.parse(xhr.responseText);
                    bootbox.alert("Error 401 - Unauthorized: " + responseText.message);

                    $("#loginname").html("noname");
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

    var routeuserindex = crossroads.addRoute('', function () {
        if (!sessionStorage.token) {
            window.location.href = "#login";
            return;
        }
        $.ajax({
            type: "GET",
            url: 'assets/api/profile',
            dataType: "json",
            success: function (data) {
                console.log(data);
                $.get('assets/js/templates/user-profile.handlebars').then(function (src) {
                    var template = Handlebars.compile(src);
                    var html = template(data);

                    $("#divcontent").empty();
                    $("#divcontent").html(html);

                    $(".page-title").empty();
                    $(".page-title").append("Profile");

                    $(".breadcrumb").empty();
                    $(".breadcrumb").append("<li class='breadcrumb-item'><a href='#profile'>Home</a></li>");
                    $(".breadcrumb").append("<li class='breadcrumb-item active'>Profile</li>");

                })
            },
            error: function (xhr, statusText, err) {
                //console.log("hello error");
                console.log(xhr);
                console.log(statusText);
                console.log(err);
                if (xhr.status == 401) {
                    //response text from the server if there is any
                    var responseText = JSON.parse(xhr.responseText);
                    bootbox.alert("Error 401 - Unauthorized: " + responseText.message);

                    $("#loginname").html("noname");
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

    var routeuserlist = crossroads.addRoute('/userlist', function () {
        if (!sessionStorage.token) {
            window.location.href = "#login";
            return;
        }
        $.ajax({
            type: "GET",
            url: 'assets/api/userlist',
            dataType: "json",
            success: function (data) {
                console.log("hello");
                $.get('assets/js/templates/user-list.handlebars').then(function (src) {
                    var template = Handlebars.compile(src);
                    var html = template({ "userlist": data });
                    console.log(data);
                    $("#divcontent").empty();
                    $("#divcontent").html(html);

                    $(".page-title").empty();
                    $(".page-title").append("User List");

                    $(".breadcrumb").empty();
                    $(".breadcrumb").append("<li class='breadcrumb-item'><a href='#profile'>Home</a></li>");
                    $(".breadcrumb").append("<li class='breadcrumb-item active'>User List</li>");

                })
            },
            error: function (xhr, statusText, err) {
                //console.log("hello error");
                console.log(xhr);
                console.log(statusText);
                console.log(err);
                if (xhr.status == 401) {
                    //response text from the server if there is any
                    var responseText = JSON.parse(xhr.responseText);
                    bootbox.alert("Error 401 - Unauthorized: " + responseText.message);

                    $("#loginname").html("noname");
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


    hasher.initialized.add(parseHash); //parse initial hash
    hasher.changed.add(parseHash); //parse hash changes
    hasher.init(); //start listening for history change

});