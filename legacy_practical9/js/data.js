
"use strict";

// 1. Configuration
const DATASETS = {
    events: {
        url: "data/events.json",
        filterKey: "category",
        sortKey: "date",
        titleKey: "title",
        fields: [
            ["Date", "date"],
            ["Venue", "venue"]
        ]
    },
    students: {
        url: "data/students.json",
        filterKey: "course",
        sortKey: "name",
        titleKey: "name",
        fields: [
            ["Course", "course"],
            ["Year", "year"],
            ["Status", "status"]
        ]
    },
    faqs: {
        url: "data/faqs.json",
        filterKey: "category",
        sortKey: "question",
        titleKey: "question",
        fields: [
            ["Answer", "answer"],
            ["Category", "category"]
        ]
    }
};

const PAGE_SIZE = 6;

// 2. Page state
let allRecords = [];
let currentPage = 1;
let currentDataset = "events";

// 3. DOM references
const datasetSelect = document.getElementById("datasetSelect");
const searchInput = document.getElementById("searchInput");
const filterSelect = document.getElementById("filterSelect");
const sortSelect = document.getElementById("sortSelect");
const statusMessage = document.getElementById("statusMessage");
const resultsCount = document.getElementById("resultsCount");
const recordsContainer = document.getElementById("recordsContainer");
const previousBtn = document.getElementById("previousBtn");
const nextBtn = document.getElementById("nextBtn");
const pageInfo = document.getElementById("pageInfo");

// 4. Fetch JSON data
async function loadData(datasetName) {
    currentDataset = datasetName;
    currentPage = 1;
    allRecords = [];

    recordsContainer.replaceChildren();
    resultsCount.textContent = "";
    statusMessage.textContent = "Loading records...";
    statusMessage.hidden = false;
    statusMessage.style.color = "#4B3621";

    previousBtn.disabled = true;
    nextBtn.disabled = true;
    pageInfo.textContent = "Loading...";

    filterSelect.replaceChildren();
    filterSelect.add(new Option("All categories", ""));

    try {
        const response = await fetch(DATASETS[datasetName].url);

        if (!response.ok) {
            throw new Error("Could not load the JSON file. Check its path.");
        }

        const data = await response.json();

        if (!Array.isArray(data)) {
            throw new Error("The JSON file must contain an array of records.");
        }

        allRecords = data;
        populateFilters();
        statusMessage.textContent = "Data loaded successfully.";
        statusMessage.style.color = "#236B3B";

        renderRecords();
    } catch (error) {
        statusMessage.textContent =
            "Error loading data: " + error.message +
            " Check your JSON file and run this page with Live Server.";
        statusMessage.style.color = "#B42318";

        recordsContainer.replaceChildren();
        resultsCount.textContent = "";
        pageInfo.textContent = "Page 0";
        previousBtn.disabled = true;
        nextBtn.disabled = true;

        console.error("Fetch error:", error);
    }
}

// 5. Build the filter options from the fetched records
function populateFilters() {
    const config = DATASETS[currentDataset];
    const values = [...new Set(
        allRecords.map(record => String(record[config.filterKey] ?? ""))
            .filter(Boolean)
    )].sort((a, b) => a.localeCompare(b));

    filterSelect.replaceChildren();
    filterSelect.add(new Option("All categories", ""));

    values.forEach(value => {
        filterSelect.add(new Option(value, value));
    });
}

// 6. Search, filter and sort the records
function getProcessedRecords() {
    const config = DATASETS[currentDataset];
    const searchTerm = searchInput.value.trim().toLowerCase();
    const selectedFilter = filterSelect.value;
    const sortOrder = sortSelect.value;

    let records = allRecords.filter(record => {
        const matchesSearch = Object.values(record).some(value =>
            String(value).toLowerCase().includes(searchTerm)
        );

        const matchesFilter = !selectedFilter ||
            String(record[config.filterKey]) === selectedFilter;

        return matchesSearch && matchesFilter;
    });

    if (sortOrder !== "default") {
        const direction = sortOrder === "asc" ? 1 : -1;
        const key = config.sortKey;

        records.sort((a, b) =>
            String(a[key] ?? "").localeCompare(
                String(b[key] ?? ""),
                undefined,
                { numeric: true, sensitivity: "base" }
            ) * direction
        );
    }

    return records;
}

// 7. Safely create text elements
function createTextElement(tag, text, className = "") {
    const element = document.createElement(tag);
    element.textContent = String(text ?? "");

    if (className) {
        element.className = className;
    }

    return element;
}

// 8. Render a single card
function createRecordCard(record) {
    const config = DATASETS[currentDataset];
    const card = document.createElement("article");
    card.className = "record-card";

    card.appendChild(
        createTextElement("h2", record[config.titleKey])
    );

    const category = record[config.filterKey];

    if (category) {
        card.appendChild(
            createTextElement("span", category, "record-category")
        );
    }

    config.fields.forEach(([label, key]) => {
        const value = record[key];

        if (value !== undefined && value !== null) {
            card.appendChild(
                createTextElement("p", label + ": " + value)
            );
        }
    });

    return card;
}

// 9. Render current page and update pagination
function renderRecords() {
    const records = getProcessedRecords();
    const totalRecords = records.length;
    const totalPages = Math.ceil(totalRecords / PAGE_SIZE);

    if (totalPages === 0) {
        currentPage = 1;
    } else if (currentPage > totalPages) {
        currentPage = totalPages;
    }

    const startIndex = (currentPage - 1) * PAGE_SIZE;
    const pageRecords = records.slice(startIndex, startIndex + PAGE_SIZE);

    recordsContainer.replaceChildren();

    pageRecords.forEach(record => {
        recordsContainer.appendChild(createRecordCard(record));
    });

    resultsCount.textContent =
        "Showing " + (totalRecords === 0 ? 0 : startIndex + 1) +
        "–" + Math.min(startIndex + PAGE_SIZE, totalRecords) +
        " of " + totalRecords + " records";

    pageInfo.textContent =
        "Page " + (totalPages === 0 ? 0 : currentPage) +
        " of " + totalPages;

    previousBtn.disabled = currentPage <= 1;
    nextBtn.disabled = totalPages === 0 || currentPage >= totalPages;

    if (totalRecords === 0) {
        statusMessage.textContent =
            "No records found. Try a different search or filter.";
        statusMessage.style.color = "#B42318";
    } else {
        statusMessage.textContent = "Data loaded successfully.";
        statusMessage.style.color = "#236B3B";
    }
}

// 10. Event handlers
datasetSelect.addEventListener("change", () => {
    searchInput.value = "";
    sortSelect.value = "default";
    loadData(datasetSelect.value);
});

searchInput.addEventListener("input", () => {
    currentPage = 1;
    renderRecords();
});

filterSelect.addEventListener("change", () => {
    currentPage = 1;
    renderRecords();
});

sortSelect.addEventListener("change", () => {
    currentPage = 1;
    renderRecords();
});

previousBtn.addEventListener("click", () => {
    if (currentPage > 1) {
        currentPage--;
        renderRecords();
    }
});

nextBtn.addEventListener("click", () => {
    const totalPages = Math.ceil(
        getProcessedRecords().length / PAGE_SIZE
    );

    if (currentPage < totalPages) {
        currentPage++;
        renderRecords();
    }
});

// 11. Start the application
loadData(datasetSelect.value);