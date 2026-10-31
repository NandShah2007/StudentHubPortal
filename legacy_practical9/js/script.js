/* =====================================================
   STUDENT HUB PORTAL
   PRACTICAL 4 JAVASCRIPT
===================================================== */

document.addEventListener("DOMContentLoaded", function () {


    /* =================================================
       1. HAMBURGER MENU
    ================================================= */

    const menuToggle =
        document.getElementById("menuToggle");

    const navbarMenu =
        document.getElementById("navbarMenu");


    if (menuToggle && navbarMenu) {

        menuToggle.addEventListener("click", function () {

            navbarMenu.classList.toggle("show-menu");

            const isOpen =
                navbarMenu.classList.contains("show-menu");

            menuToggle.setAttribute(
                "aria-expanded",
                isOpen
            );

            if (isOpen) {

                menuToggle.innerHTML =
                    '<i class="fa-solid fa-xmark"></i>';

            } else {

                menuToggle.innerHTML =
                    '<i class="fa-solid fa-bars"></i>';

            }

        });

    }


    /* =================================================
       2. NOTIFICATION BANNER
    ================================================= */

    const notificationBanner =
        document.getElementById("notificationBanner");

    const closeNotification =
        document.getElementById("closeNotification");


    if (closeNotification && notificationBanner) {

        closeNotification.addEventListener(
            "click",
            function () {

                notificationBanner.style.display = "none";

            }
        );

    }


    /* =================================================
       3. MODAL
    ================================================= */

    const detailsBtn =
        document.getElementById("detailsBtn");

    const detailsModal =
        document.getElementById("detailsModal");

    const closeModal =
        document.getElementById("closeModal");


    function openModal() {

        detailsModal.classList.add("show");

        detailsModal.setAttribute(
            "aria-hidden",
            "false"
        );

    }


    function hideModal() {

        detailsModal.classList.remove("show");

        detailsModal.setAttribute(
            "aria-hidden",
            "true"
        );

    }


    if (detailsBtn) {

        detailsBtn.addEventListener(
            "click",
            openModal
        );

    }


    if (closeModal) {

        closeModal.addEventListener(
            "click",
            hideModal
        );

    }


    if (detailsModal) {

        detailsModal.addEventListener(
            "click",
            function (event) {

                if (event.target === detailsModal) {

                    hideModal();

                }

            }
        );

    }


    /* =================================================
       4. CLOSE MODAL WITH ESCAPE KEY
    ================================================= */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape" &&
                detailsModal &&
                detailsModal.classList.contains("show")
            ) {

                hideModal();

            }

        }
    );


    /* =================================================
       5. FAQ ACCORDION
    ================================================= */

    const faqQuestions =
        document.querySelectorAll(".faq-question");


    faqQuestions.forEach(function (question) {

        question.addEventListener(
            "click",
            function () {

                const answer =
                    question.nextElementSibling;

                const isOpen =
                    question.classList.contains("open");


                /* Close all other FAQ items */

                faqQuestions.forEach(
                    function (otherQuestion) {

                        if (otherQuestion !== question) {

                            otherQuestion.classList.remove(
                                "open"
                            );

                            otherQuestion.setAttribute(
                                "aria-expanded",
                                "false"
                            );

                            const otherAnswer =
                                otherQuestion.nextElementSibling;

                            if (otherAnswer) {

                                otherAnswer.style.maxHeight =
                                    null;

                            }

                        }

                    }
                );


                /* Open / Close selected FAQ */

                if (!isOpen) {

                    question.classList.add("open");

                    question.setAttribute(
                        "aria-expanded",
                        "true"
                    );

                    answer.style.maxHeight =
                        answer.scrollHeight + "px";

                } else {

                    question.classList.remove("open");

                    question.setAttribute(
                        "aria-expanded",
                        "false"
                    );

                    answer.style.maxHeight = null;

                }

            }
        );

    });


    /* =================================================
       6. SLIDER
    ================================================= */

    const slides =
        document.querySelectorAll(".slide");

    const dots =
        document.querySelectorAll(".dot");

    const nextSlide =
        document.getElementById("nextSlide");

    const previousSlide =
        document.getElementById("previousSlide");


    let currentSlide = 0;


    function showSlide(index) {

        if (index >= slides.length) {

            currentSlide = 0;

        } else if (index < 0) {

            currentSlide =
                slides.length - 1;

        } else {

            currentSlide = index;

        }


        slides.forEach(
            function (slide) {

                slide.classList.remove("active");

            }
        );


        dots.forEach(
            function (dot) {

                dot.classList.remove("active-dot");

            }
        );


        if (slides[currentSlide]) {

            slides[currentSlide]
                .classList.add("active");

        }


        if (dots[currentSlide]) {

            dots[currentSlide]
                .classList.add("active-dot");

        }

    }


    if (nextSlide) {

        nextSlide.addEventListener(
            "click",
            function () {

                showSlide(currentSlide + 1);

            }
        );

    }


    if (previousSlide) {

        previousSlide.addEventListener(
            "click",
            function () {

                showSlide(currentSlide - 1);

            }
        );

    }


    dots.forEach(
        function (dot) {

            dot.addEventListener(
                "click",
                function () {

                    const slideNumber =
                        Number(
                            dot.getAttribute("data-slide")
                        );

                    showSlide(slideNumber);

                }
            );

        }
    );


    /* Automatic slider */

    if (slides.length > 0) {

        setInterval(
            function () {

                showSlide(currentSlide + 1);

            },
            5000
        );

    }


    /* =================================================
       7. DARK / LIGHT THEME
    ================================================= */

    const themeToggle =
        document.getElementById("themeToggle");


    /* Check saved theme */

    const savedTheme =
        localStorage.getItem("studentHubTheme");


    if (savedTheme === "dark") {

        document.body.classList.add("dark-mode");

        if (themeToggle) {

            themeToggle.innerHTML =
                '<i class="fa-solid fa-sun"></i>';

            themeToggle.setAttribute(
                "aria-label",
                "Switch to light theme"
            );

        }

    }


    if (themeToggle) {

        themeToggle.addEventListener(
            "click",
            function () {

                document.body.classList.toggle(
                    "dark-mode"
                );


                const darkMode =
                    document.body.classList.contains(
                        "dark-mode"
                    );


                /* Save theme */

                localStorage.setItem(
                    "studentHubTheme",
                    darkMode ? "dark" : "light"
                );


                /* Modify button icon */

                if (darkMode) {

                    themeToggle.innerHTML =
                        '<i class="fa-solid fa-sun"></i>';

                    themeToggle.setAttribute(
                        "aria-label",
                        "Switch to light theme"
                    );

                } else {

                    themeToggle.innerHTML =
                        '<i class="fa-solid fa-moon"></i>';

                    themeToggle.setAttribute(
                        "aria-label",
                        "Switch to dark theme"
                    );

                }

            }
        );

    }

});