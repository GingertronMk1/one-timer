document.querySelectorAll("[data-expiry-form]").forEach((form) => {
    const duration = form.elements.expires_in;
    const unit = form.elements.expires_unit;
    const preview = form.querySelector("[data-expiry-preview]");

    function updateExpiry() {
        const amount = Number(duration.value);
        if (!duration.value || !Number.isSafeInteger(amount) || amount < 1) {
            preview.textContent = "";
            return;
        }

        const expiry = new Date();
        switch (unit.value) {
            case "minutes":
                expiry.setUTCMinutes(expiry.getUTCMinutes() + amount);
                break;
            case "hours":
                expiry.setUTCHours(expiry.getUTCHours() + amount);
                break;
            case "days":
                expiry.setUTCDate(expiry.getUTCDate() + amount);
                break;
            case "weeks":
                expiry.setUTCDate(expiry.getUTCDate() + amount * 7);
                break;
            case "months":
                expiry.setUTCMonth(expiry.getUTCMonth() + amount);
                break;
            case "years":
                expiry.setUTCFullYear(expiry.getUTCFullYear() + amount);
                break;
            default:
                preview.textContent = "";
                return;
        }

        preview.textContent = Number.isNaN(expiry.getTime())
            ? ""
            : `Estimated expiry: ${expiry.toLocaleString()} (your local time)`;
    }

    duration.addEventListener("input", updateExpiry);
    unit.addEventListener("change", updateExpiry);
    updateExpiry();
    setInterval(updateExpiry, 60000);
});
