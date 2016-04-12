
// Code for adding users to groups (AJAX search)

$('.adduser_search').focus(function() {

    $(this).keyup();
})
$('.adduser_search').keyup(function() {

    searchText = $(this).val();

    $.ajax({
        context: this,
        type: "POST",
        url: Routing.generate('usersNotInGroupSearch'),
        dataType: "html",
        data: {
            groupId: $(this).data('groupid'),
            searchText : searchText},
        success : function(response)
        {
            $(this).next('.adduser_search_results').html(response).show();
            return true;
        }
    });
});