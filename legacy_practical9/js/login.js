document
    .getElementById("loginForm")
    .addEventListener("submit", function (e) {

        e.preventDefault();

        let user = document.getElementById("username").value;

        let pass = document.getElementById("password").value;

        if (user === "Nand" && pass === "123") {

            window.location.href = "dashboard.html";

        }
        else {

            document.getElementById("message").innerHTML = "Invalid Credentials";

            document.getElementById("message").style.color = "red";

        }

    });