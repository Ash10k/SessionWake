const IP = "192.168.0.107";
$(document).ready(()=>{
    if (!Cookies.get("username")){
        alert("Session Expired");
        window.location.href = "./login.html";
    } else {
        $("#heading").text("Active sessions of "+Cookies.get("username"));
        fetchSessions();
    }
    
});

var fetchSessions = () => {
    $.ajax({        
        type: "POST",        
        url: `http://${IP}/SessionWake/php/api/v1/get.sessions.php`,
        headers: {
            "Authorization": Cookies.get("token")
        }
    }).done((responseBodyJSON) => {                        
        var responseBody = JSON.parse(responseBodyJSON);
        console.log(responseBody);
        var token = Cookies.get("token");
        responseBody.sessions.forEach((session, i)=>{
            if (token.includes(session.refreshId)){
                $("#sessionList").append(`
                    <li id="li${i}">
                        <div>
                            <p><b>DEVICE: </b>${session.device}</p>
                            <p>${session.moment} <a onclick="destroySession('${session.refreshId}', ${i}, true)" href="#">LOGOUT FROM THIS DEVICE</a></p>                        
                            <br>
                        </div>
                    </li>
            `   );
                $("#heading").append(`
                    <a onclick="destroyAllSessions('${session.refreshId}')" href="#">LOGOUT FROM ALL OTHER DEVICES</a>
                `);
            } else {
                $("#sessionList").append(`
                    <li class="other" id="li${i}">
                        <div>
                            <p><b>DEVICE: </b>${session.device}</p>
                            <p>${session.moment} <a onclick="destroySession('${session.refreshId}', ${i})" href="#">LOGOUT</a></p>                        
                            <br>
                        </div>
                    </li>
                `);
            }
        });                
    }).fail((xhr) => {
        console.log("failed: ", xhr.status);
        switch(xhr.status){
            case 401:
                $("#result").text("You are not authorized to view this data");
                Cookies.remove("token");
                Cookies.remove("username");
                break;
        }        
    });
}

var destroySession = (refreshId, i, self) => {
    $.ajax({        
        type: "POST",        
        url: `http://${IP}/SessionWake/php/api/v1/destroy.session.php`,
        headers: {
            "Authorization": Cookies.get("token")
        },
        data: JSON.stringify({
            refreshId: refreshId
        })
    }).done((responseBodyJSON) => {                        
        var responseBody = JSON.parse(responseBodyJSON);
        console.log(responseBody);
        if (responseBody.statusCode==1){
            $(`#li${i}`).remove();
            alert("Session Ended");
            if (self){
                Cookies.remove("token");
                Cookies.remove("username");
                window.location.href = "./login.html";
            }            
        }   
    }).fail((xhr) => {
        console.log("failed: ", xhr.status);
        switch(xhr.status){
            case 401:
                $("#result").text("You are not authorized to view this data");
                break;
        }        
    });    
}

var destroyAllSessions = (refreshId) => {
    $.ajax({        
        type: "POST",        
        url: `http://${IP}/SessionWake/php/api/v1/destroy.sessions.php`,
        headers: {
            "Authorization": Cookies.get("token")
        },
        data: JSON.stringify({
            refreshId: refreshId
        })
    }).done((responseBodyJSON) => {                        
        var responseBody = JSON.parse(responseBodyJSON);
        console.log(responseBody);
        if (responseBody.statusCode==1){
            $(".other").remove();
            alert("All other Sessions were Ended");            
        }   
    }).fail((xhr) => {
        console.log("failed: ", xhr.status);
        switch(xhr.status){
            case 401:
                $("#result").text("You are not authorized to view this data");
                break;
        }        
    });    
}