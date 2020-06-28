function loadTable() {
    $.ajax({
        type: "GET",
        url: 'assets/api/ticket',
        dataType: "json",
        success: function (data) {
            $.get('assets/js/templates/make-booking.handlebars').then(function (src) {
                var template = Handlebars.compile(src);
                var html = template({
                    "ticketlist": data
                });
                // $('body').empty();
                // // $('body').html(html);
                // $('body').append(html);
                $("#divcontent").empty();
                $("#divcontent").html(html);

            })

        },
        error: function (xhr, statusText, err) {
            $('body').empty();
            console.log("error");
            console.log(xhr);
            console.log(statusText);
            console.log(err);
        }
    })
}

$(function () {

    function parseHash(newHash, oldHash) {
        crossroads.parse(newHash);
    }
    crossroads.ignoreState = true;

    var ticketRoute = crossroads.addRoute('/make-booking', function () {

        loadTable();


    });

});

function makeBooking(id) {


    // var nameTxt = $(this.value);
    var token = sessionStorage.getItem("token");
    // alert(token);

    var obj = new Object();
    obj.ticket_id = id;
    Swal.fire({
        text: "CONFIRM TO BOOK A TICKET",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'YES'
    }).then((result) => {
        if (result.value) {
            $.ajax({
                type: "POST",
                headers: {
                    "Authorization": "Bearer " + token,
                },
                url: 'assets/api/createBooking',
                dataType: "json",
                data: JSON.stringify(obj),
                success: function (data) {
                    //Refresh table
                    if (data.bookingStatus != "Full") {
                        loadTable();
                        Swal.fire({
                            type: 'success',
                            text: 'SUCCESS!',
                        })
                    } else {
                        Swal.fire({
                            type: 'error',
                            text: 'FULL!',
                        })
                    }
                },
                error: function (xhr, statusText, err) {
                    $('body').empty();
                    // console.log("error");
                    console.log(xhr);
                    console.log(statusText);
                    console.log(err);
                }
            });
        }
    })



    // console.log(data);
    // alert(nameTxt);

}