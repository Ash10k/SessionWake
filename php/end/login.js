const IP = "192.168.0.107";
$(document).ready(()=>{
    if (Cookies.get("token")){
        window.location.href = "./";
    }
});
var login = () => {
    username = document.getElementById("username").value;
    password = document.getElementById("password").value;
    $.post(`http://${IP}/SessionWake/php/api/v1/login.php`, JSON.stringify({
        username: username,
        password: password
    }), (data)=>{
        console.log(data);
        var body = JSON.parse(data);
        if (body.statusCode == 1){
            Cookies.set("username", body.username, {expires: 7});
            Cookies.set("token", body.token, {expires: 7});
            window.location.href = "./";
        } else {
            $("#result").text("FAILED TO LOGIN: "+body.msg);
        }
    })
}