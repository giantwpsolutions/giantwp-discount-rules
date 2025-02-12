import apiFetch from "@wordpress/api-fetch";

export const saveData = {
    async saveCoupon(data) {
        try {
            // ✅ Ensure conditions are formatted properly
            const formattedConditions = Array.isArray(data.conditions)
                ? data.conditions.map((c) => ({
                    field: c.field,
                    operator: c.operator,
                    value: Array.isArray(c.value) ? c.value : [c.value]  // ✅ Always send as an array
                }))
                : [];

            console.log("Final Conditions Before Sending:", formattedConditions);

            const response = await apiFetch({
                path: `${pluginData.restUrl}save-data`,
                method: "POST",
                headers: {
                    "X-WP-Nonce": pluginData.nonce,
                },
                data: {
                    discountType: data.discountType,
                    couponName: data.couponName,
                    fpDiscountType: data.fpDiscountType,
                    discountValue: data.discountValue,
                    maxValue: data.maxValue,
                    schedule: {
                        enableSchedule: data.schedule?.enableSchedule || false,
                        startDate: data.schedule?.startDate || null,
                        endDate: data.schedule?.endDate || null,
                    },
                    usageLimits: {
                        enableUsage: data.usageLimits?.enableUsage ?? false,
                        usageLimitsCount: data.usageLimits?.usageLimitsCount ?? 0,
                    },
                    autoApply: data.autoApply || false,
                    enableConditions: data.enableConditions || false,
                    conditionsApplies: data.conditionsApplies ?? "any",
                    conditions: formattedConditions,
                },
            });

            return response;
        } catch (error) {
            console.error("Error saving coupon data:", error);
            throw new Error(error.message);
        }
    },
};
