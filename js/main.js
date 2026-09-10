

document.addEventListener("DOMContentLoaded", function () {

    

    const registerForm = document.querySelector(".register-form");

    if (registerForm && window.location.href.includes("register")) {

        registerForm.addEventListener("submit", function (e) {

            const name = registerForm.querySelector('input[type="text"]');
            const email = registerForm.querySelector('input[type="email"]');
            const phone = registerForm.querySelector('input[type="tel"]');
            const passwords = registerForm.querySelectorAll('input[type="password"]');
            const terms = registerForm.querySelector('input[type="checkbox"]');

            if (name.value.trim() === "") {
                alert("Please enter your full name.");
                e.preventDefault();
                return;
            }

            if (email.value.trim() === "") {
                alert("Please enter your email.");
                e.preventDefault();
                return;
            }

            if (phone.value.trim() === "") {
                alert("Please enter your phone number.");
                e.preventDefault();
                return;
            }

            if (passwords[0].value.length < 6) {
                alert("Password must be at least 6 characters.");
                e.preventDefault();
                return;
            }

            if (passwords[0].value !== passwords[1].value) {
                alert("Passwords do not match.");
                e.preventDefault();
                return;
            }

            if (!terms.checked) {
                alert("Please accept the Terms and Conditions.");
                e.preventDefault();
                return;
            }

            alert("Registration Successful!");

        });

    }

    
    if (registerForm && window.location.href.includes("login")) {

        registerForm.addEventListener("submit", function (e) {

            const email = registerForm.querySelector('input[type="email"]');
            const password = registerForm.querySelector('input[type="password"]');

            if (email.value.trim() === "" || password.value.trim() === "") {

                alert("Please fill in all fields.");

                e.preventDefault();

            }

        });

    }

    if (registerForm && window.location.href.includes("verify")) {

        registerForm.addEventListener("submit", function (e) {

            const code = registerForm.querySelector('input[type="text"]');

            if (code.value.length !== 6) {

                alert("Verification code must be 6 digits.");

                e.preventDefault();

                return;

            }

            alert("Email Verified Successfully!");

        });

    }



    const passwordFields = document.querySelectorAll('input[type="password"]');

    passwordFields.forEach(function(field){

        field.addEventListener("dblclick",function(){

            if(field.type==="password"){

                field.type="text";

            }else{

                field.type="password";

            }

        });

    });

   

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {

        anchor.addEventListener("click", function (e) {

            const target = document.querySelector(this.getAttribute("href"));

            if(target){

                e.preventDefault();

                target.scrollIntoView({

                    behavior:"smooth"

                });

            }

        });

    });

});