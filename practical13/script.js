
const API_URL = "api.php";

// Security token (CSRF) printed into the page by index.php
const CSRF_TOKEN = (document.querySelector('meta[name="csrf-token"]') || {}).content || "";

const form = document.getElementById("studentForm");
const nameInput = document.getElementById("full_name");
const emailInput = document.getElementById("email");
const departmentInput = document.getElementById("department");
const table = document.getElementById("studentTable");
const message = document.getElementById("message");
const loading = document.getElementById("loading");
const saveButton = document.getElementById("saveButton");
const cancelButton = document.getElementById("cancelButton");
const formTitle = document.getElementById("formTitle");

let editingId = null;
let busy = false;

function showMessage(text, type = "") {
    message.textContent = text;
    message.className = type;
}

async function requestAPI(url, options = {}) {
    const response = await fetch(url, options);
    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || `Request failed (${response.status})`);
    }

    return result;
}

function setBusy(value) {
    busy = value;
    saveButton.disabled = value;
    cancelButton.disabled = value;
    loading.hidden = !value;
}

function addCell(row, value) {
    const cell = document.createElement("td");
    cell.textContent = value ?? "";
    row.appendChild(cell);
    return cell;
}

function createButton(label, className, onClick) {
    const button = document.createElement("button");
    button.type = "button";
    button.textContent = label;
    button.className = className;
    button.addEventListener("click", onClick);
    return button;
}

async function loadStudents() {
    loading.hidden = false;

    try {
        const result = await requestAPI(API_URL);
        table.replaceChildren();

        if (!result.data || result.data.length === 0) {
            const row = document.createElement("tr");
            const cell = addCell(row, "No students found. Add your first student.");
            cell.colSpan = 5;
            table.appendChild(row);
            return;
        }

        result.data.forEach(student => {
            const row = document.createElement("tr");

            addCell(row, student.id);
            addCell(row, student.full_name);
            addCell(row, student.email);
            addCell(row, student.department);

            const actionCell = document.createElement("td");
            const actions = document.createElement("div");
            actions.className = "actions";

            actions.appendChild(createButton("Edit", "edit", () => {
                editingId = student.id;
                nameInput.value = student.full_name;
                emailInput.value = student.email;
                departmentInput.value = student.department;

                formTitle.textContent = "Edit Student";
                saveButton.textContent = "Update Student";
                cancelButton.hidden = false;
                showMessage("Editing student ID " + student.id);
                nameInput.focus();
            }));

            actions.appendChild(createButton("Delete", "delete", async () => {
                if (!confirm("Delete " + student.full_name + "?")) return;

                try {
                    setBusy(true);

                    await requestAPI(API_URL, {
                        method: "POST",
                        headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": CSRF_TOKEN
            },
                        body: JSON.stringify({
                            action: "delete",
                            id: student.id
                        })
                    });

                    if (String(editingId) === String(student.id)) {
                        resetForm();
                    }

                    showMessage("Student deleted successfully.", "success");
                    await loadStudents();
                } catch (error) {
                    showMessage(error.message, "error");
                } finally {
                    setBusy(false);
                }
            }));

            actionCell.appendChild(actions);
            row.appendChild(actionCell);
            table.appendChild(row);
        });
    } catch (error) {
        table.replaceChildren();

        const row = document.createElement("tr");
        const cell = addCell(row, "Could not load students. Please try again.");
        cell.colSpan = 5;
        table.appendChild(row);

        showMessage(error.message, "error");
    } finally {
        loading.hidden = true;
    }
}

function resetForm() {
    form.reset();
    editingId = null;
    formTitle.textContent = "Add Student";
    saveButton.textContent = "Add Student";
    cancelButton.hidden = true;
}

form.addEventListener("submit", async event => {
    event.preventDefault();
    if (busy) return;

    const payload = {
        action: editingId === null ? "add" : "edit",
        full_name: nameInput.value.trim(),
        email: emailInput.value.trim(),
        department: departmentInput.value.trim()
    };

    if (editingId !== null) {
        payload.id = editingId;
    }

    try {
        setBusy(true);
        showMessage("Saving student...");

        const result = await requestAPI(API_URL, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": CSRF_TOKEN
            },
            body: JSON.stringify(payload)
        });

        resetForm();
        showMessage(result.message, "success");
        await loadStudents();
    } catch (error) {
        showMessage(error.message, "error");
    } finally {
        setBusy(false);
    }
});

cancelButton.addEventListener("click", () => {
    resetForm();
    showMessage("Edit cancelled.");
});

loadStudents();
