$(function () {
    var maxField = 2; //Input fields increment limitation
    var addButton = $('.add_button'); //Add button selector
    var wrapper = $('.field_wrapper'); //Input field wrapper
    var fieldHTML = '<div class="mt-2 mb-2 row"><input class="form-control m-1 col-md-2" type="text" name="periode[]" placeholder="Periode" style="width: 10%;" value=""/><input class="form-control m-1 col-md-2" type="number" name="tarif[]" placeholder="tarif" style="width: 10%;" value=""/><textarea id="avantage" name="avantage[]" rows="4" cols="50" placeholder=""></textarea><a href="javascript:void(0);" class="remove_button col-md-1"><i class="fa fa-trash text-danger" style="font-size: 25px;"></i></a></div>'; //New input field html

    var x = 1; //Initial field counter is 1

    //Once add button is clicked
    $(addButton).click(function () {
        //Check maximum number of input fields
        if (x < maxField) {
            x++; //Increment field counter
            $(wrapper).append(fieldHTML); //Add field html
        }
    });

   

    //Once remove button is clicked
    $(wrapper).on('click', '.remove_button', function (e) {
        e.preventDefault();
        $(this).parent('div').remove(); //Remove field html
        x--; //Decrement field counter
    });
});
