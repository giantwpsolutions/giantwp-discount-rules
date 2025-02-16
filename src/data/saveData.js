import apiFetch from "@wordpress/api-fetch";

export const saveData = {
    async saveCoupon(newData) {
        try {
            // ✅ Fetch existing data before appending
            const existingData = await apiFetch({
                path: `${pluginData.restUrl}get-discounts`,
                method: "GET",
                headers: {
                    "X-WP-Nonce": pluginData.nonce,
                },
            });

            const previousDiscounts = Array.isArray(existingData) ? existingData : [];

            // ✅ Ensure conditions are formatted properly
            const formattedConditions = Array.isArray(newData.conditions)
                ? newData.conditions.map((c) => ({
                    field: c.field || "",
                    operator: c.operator || "",
                    value: Array.isArray(c.value) ? c.value : [c.value],
                }))
                : [];

            console.log("Final Conditions Before Sending:", formattedConditions);

            // ✅ Ensure required fields are provided
            if (!newData.couponName || !newData.discountType) {
                throw new Error("Missing required fields: couponName or discountType");
            }

            // ✅ Prepare new discount entry
            const newDiscount = {
                discountType: newData.discountType,
                status: ["on", "off"].includes(newData.status) ? newData.status : "on",
                couponName: newData.couponName,
                fpDiscountType: newData.fpDiscountType || "fixed",
                discountValue: newData.discountValue || 0,
                maxValue: newData.maxValue || null,
                schedule: {
                    enableSchedule: newData.schedule?.enableSchedule || false,
                    startDate: newData.schedule?.startDate || null,
                    endDate: newData.schedule?.endDate || null,
                },
                usageLimits: {
                    enableUsage: newData.usageLimits?.enableUsage ?? false,
                    usageLimitsCount: newData.usageLimits?.usageLimitsCount ?? 0,
                },
                autoApply: newData.autoApply || false,
                enableConditions: newData.enableConditions || false,
                conditionsApplies: newData.conditionsApplies ?? "any",
                conditions: formattedConditions,
            };

            // ✅ Append new discount data
            const updatedDiscounts = [...previousDiscounts, newDiscount];

            // ✅ Save the updated data
            const response = await apiFetch({
                path: `${pluginData.restUrl}save-data`,
                method: "POST",
                headers: {
                    "X-WP-Nonce": pluginData.nonce,
                },
                data: newDiscount, // ✅ Send full array with appended data
            });

            return response;
        } catch (error) {
            console.error("Error saving coupon data:", error);
            throw new Error(error.message);
        }
    },
};
