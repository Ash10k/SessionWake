const IP = "localhost";
$(document).ready(()=>{

});
var register = () => {
    username = document.getElementById("username").value;
    password = document.getElementById("password").value;
    $.post(`http://${IP}/SessionWake/php/api/v1/register.consumer.php`, JSON.stringify({
        username: username,
        password: password
    }), (data)=>{
        console.log(data);
        var body = JSON.parse(data);
        if (body.statusCode == 1){
            $("#result").text("REGISTERED");
        } else {
            $("#result").text("FAILED TO REGISTER: "+body.msg);
        }
    })
}