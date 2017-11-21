function rand_next(){
    $("#background").css("background","url('src/background"+String(Math.floor(Math.random()*25))+".jpg') no-repeat center center");
    $("#background").fadeIn(500,
        function(){
            setInterval(
                function(){
                    $("#background").fadeOut(500,
                        function(){
                            $("#background").css("background","url('src/background"+String(Math.floor(Math.random()*25))+".jpg') no-repeat center center");
                            $("#background").fadeIn(500);
                        }
                    );
                },10000
            );
        }
    );
}