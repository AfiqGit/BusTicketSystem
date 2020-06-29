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

    Handlebars.registerHelper("displaystatus", function(status){
        if (status == 0){
            return "<span class='badge badge-danger'>Pending</span>";
        } else if (status == 1) {
            return "<span class='badge badge-success'>Accepted</span>";
        }
    });

    crossroads.ignoreState = true;

    crossroads.addRoute('', function(){
        if (!sessionStorage.token) {
            window.location.href = "#login";
            return;
        }
        window.location.href = "#home";
    });

    crossroads.addRoute('/record', function () {
        if (!sessionStorage.token) {
            window.location.href = "#login";
            return;
        }
        
        $.ajax({
            type: "GET",
            url: 'assets/api/booking',
            dataType: "json",
            success: function (data) {
                $.get('assets/js/templates/booking.handlebars').then(function (src) {
                    var template = Handlebars.compile(src);
                    var html = template({ "bookings": data });
                    $("#loginname").html(data.username);
                    $("#divcontent").empty();
                    $("#divcontent").html(html);
                })
            },
            error: function (xhr, statusText, err) {
                console.log("error");
                console.log(xhr);
                console.log(statusText);
                console.log(err);
            }
        })

        $(".breadcrumb").empty();
        $(".breadcrumb").append("<li class='breadcrumb-item'><a href='#profile'>Home</a></li>");
        $(".breadcrumb").append("<li class='breadcrumb-item'>Record</li>");
    });

    hasher.initialized.add(parseHash); //parse initial hash
    hasher.changed.add(parseHash); //parse hash changes
    hasher.init(); //start listening for history change

});