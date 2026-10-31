
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registrationForm");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirmPassword");
    const strengthBar = document.getElementById("strengthBar");
    const strengthText = document.getElementById("strengthText");
    const formMessage = document.getElementById("formMessage");

    const fields = {
        fullName: document.getElementById("fullName"),
        email: document.getElementById("email"),
        mobile: document.getElementById("mobile"),
        password: password,
        confirmPassword: confirmPassword,
        course: document.getElementById("course"),
        year: document.getElementById("year")
    };

    const errors = {
        fullName: document.getElementById("nameError"),
        email: document.getElementById("emailError"),
        mobile: document.getElementById("mobileError"),
        password: document.getElementById("passwordError"),
        confirmPassword: document.getElementById("confirmPasswordError"),
        course: document.getElementById("courseError"),
        year: document.getElementById("yearError"),
        gender: document.getElementById("genderError"),
        terms: document.getElementById("termsError")
    };

    const patterns = {
        name: /^[A-Za-z]+(?:[ '-][A-Za-z]+)*$/,
        email: /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
        mobile: /^[6-9]\d{9}$/,
        upper: /[A-Z]/,
        lower: /[a-z]/,
        number: /\d/,
        special: /[^A-Za-z0-9]/
    };

    function setError(fieldName, message) {
        errors[fieldName].textContent = message;

        if (fields[fieldName]) {
            fields[fieldName].setAttribute("aria-invalid", "true");
            fields[fieldName].setAttribute(
                "aria-describedby",
                errors[fieldName].id
            );
        }
    }

    function clearError(fieldName) {
        errors[fieldName].textContent = "";

        if (fields[fieldName]) {
            fields[fieldName].removeAttribute("aria-invalid");
            fields[fieldName].removeAttribute("aria-describedby");
        }
    }

    function validateName() {
        const value = fields.fullName.value.trim();

        if (!value) {
            setError("fullName", "Please enter your full name.");
            return false;
        }

        if (!patterns.name.test(value) || value.replace(/[^A-Za-z]/g, "").length < 2) {
            setError("fullName", "Use at least 2 letters. Only letters, spaces, apostrophes and hyphens are allowed.");
            return false;
        }

        clearError("fullName");
        return true;
    }

    function validateEmail() {
        const value = fields.email.value.trim();

        if (!value) {
            setError("email", "Please enter your email address.");
            return false;
        }

        if (!patterns.email.test(value)) {
            setError("email", "Please enter a valid email address.");
            return false;
        }

        clearError("email");
        return true;
    }

    function validateMobile() {
        const value = fields.mobile.value.trim();

        if (!value) {
            setError("mobile", "Please enter your mobile number.");
            return false;
        }

        if (!patterns.mobile.test(value)) {
            setError("mobile", "Enter a valid 10-digit Indian mobile number starting with 6, 7, 8 or 9.");
            return false;
        }

        clearError("mobile");
        return true;
    }

    function validatePassword() {
        const value = password.value;

        if (!value) {
            setError("password", "Please create a password.");
            return false;
        }

        const strongEnough =
            value.length >= 8 &&
            patterns.upper.test(value) &&
            patterns.lower.test(value) &&
            patterns.number.test(value) &&
            patterns.special.test(value);

        if (!strongEnough) {
            setError(
                "password",
                "Use 8+ characters including uppercase, lowercase, a number and a special character."
            );
            return false;
        }

        clearError("password");
        return true;
    }

    function validateConfirmPassword() {
        if (!confirmPassword.value) {
            setError("confirmPassword", "Please confirm your password.");
            return false;
        }

        if (confirmPassword.value !== password.value) {
            setError("confirmPassword", "Passwords do not match.");
            return false;
        }

        clearError("confirmPassword");
        return true;
    }

    function validateSelect(fieldName, message) {
        if (!fields[fieldName].value) {
            setError(fieldName, message);
            return false;
        }

        clearError(fieldName);
        return true;
    }

    function validateGender() {
        const selected = document.querySelector('input[name="gender"]:checked');

        if (!selected) {
            errors.gender.textContent = "Please select your gender.";
            return false;
        }

        errors.gender.textContent = "";
        return true;
    }

    function validateTerms() {
        const terms = document.getElementById("terms");

        if (!terms.checked) {
            errors.terms.textContent = "You must accept the terms and conditions.";
            return false;
        }

        errors.terms.textContent = "";
        return true;
    }

    function updatePasswordStrength() {
        const value = password.value;

        if (!value) {
            strengthBar.style.width = "0";
            strengthText.textContent = "Password strength: Not entered";
            return;
        }

        let score = 0;

        if (value.length >= 8) score++;
        if (patterns.upper.test(value)) score++;
        if (patterns.lower.test(value)) score++;
        if (patterns.number.test(value)) score++;
        if (patterns.special.test(value)) score++;

        const levels = [
            { label: "Very weak", width: "20%", color: "#B42318" },
            { label: "Weak", width: "35%", color: "#D94841" },
            { label: "Fair", width: "55%", color: "#D97706" },
            { label: "Good", width: "75%", color: "#9A8B18" },
            { label: "Strong", width: "100%", color: "#237A45" }
        ];

        const level = levels[score - 1];

        strengthBar.style.width = level.width;
        strengthBar.style.background = level.color;
        strengthText.textContent = "Password strength: " + level.label;
    }

    fields.fullName.addEventListener("input", validateName);
    fields.email.addEventListener("input", validateEmail);
    fields.mobile.addEventListener("input", validateMobile);
    password.addEventListener("input", function () {
        updatePasswordStrength();
        validatePassword();

        if (confirmPassword.value) {
            validateConfirmPassword();
        }
    });

    confirmPassword.addEventListener("input", validateConfirmPassword);
    fields.course.addEventListener("change", function () {
        validateSelect("course", "Please select your course.");
    });
    fields.year.addEventListener("change", function () {
        validateSelect("year", "Please select your academic year.");
    });

    document.querySelectorAll('input[name="gender"]').forEach(function (radio) {
        radio.addEventListener("change", validateGender);
    });

    document.getElementById("terms").addEventListener("change", validateTerms);

    form.addEventListener("submit", function (event) {
        event.preventDefault();
        formMessage.textContent = "";
        formMessage.className = "";

        const checks = [
            validateName(),
            validateEmail(),
            validateMobile(),
            validatePassword(),
            validateConfirmPassword(),
            validateSelect("course", "Please select your course."),
            validateSelect("year", "Please select your academic year."),
            validateGender(),
            validateTerms()
        ];

        if (checks.includes(false)) {
            formMessage.textContent = "Please correct the highlighted fields and try again.";
            formMessage.className = "";
            const firstInvalid = form.querySelector('[aria-invalid="true"]');
            if (firstInvalid) firstInvalid.focus();
            return;
        }

        formMessage.textContent =
            "Registration form validated successfully!";
        formMessage.className = "success-message";

        // Demo only: no account is created and no data is sent to a server.
    });

    form.addEventListener("reset", function () {
        setTimeout(function () {
            Object.keys(errors).forEach(function (key) {
                errors[key].textContent = "";
            });

            Object.values(fields).forEach(function (field) {
                field.removeAttribute("aria-invalid");
                field.removeAttribute("aria-describedby");
            });

            strengthBar.style.width = "0";
            strengthText.textContent = "Password strength: Not entered";
            formMessage.textContent = "";
            formMessage.className = "";
        }, 0);
    });
});