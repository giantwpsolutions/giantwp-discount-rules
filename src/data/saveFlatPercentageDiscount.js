
import apiFetch from "@wordpress/api-fetch";
import { id } from "element-plus/es/locale/index.mjs";

export const saveFlatPercentageDiscount = {
    /**
   * Save a new discount rule
   */
    async saveCoupon(newData) {
        try {
            // Fetch existing data before appending
            const existingData = await apiFetch({
                path: `${pluginData.restUrl}get-flatpercentage-discount`,
                method: "GET",
                headers: {
                    "X-WP-Nonce": pluginData.nonce,
                },
            });

            const previousDiscounts = Array.isArray(existingData) ? existingData : [];

            // Ensure conditions are formatted properly
            const formattedConditions = Array.isArray(newData.conditions)
                ? newData.conditions.map((c) => ({
                    field: c.field || "",
                    operator: c.operator || "",
                    value: Array.isArray(c.value) ? c.value : [c.value],
                }))
                : [];

            console.log("Final Conditions Before Sending:", formattedConditions);

            const generateUniqueId = () => `dsc-${Date.now().toString(36)}-${Math.random().toString(36).substring(2, 8)}`;

            // ✅ Prepare new discount entry
            const newDiscount = {
                id: generateUniqueId(),
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
                path: `${pluginData.restUrl}save-flatpercentage-discount`,
                method: "POST",
                headers: {
                    "X-WP-Nonce": pluginData.nonce,
                },
                data: newDiscount,   // ✅ Send only the new discount entry
            });

            return response;
        } catch (error) {
            console.error("Error saving coupon data:", error);
            throw new Error(error.message);
        }
    },

    /**
     * Update an existing discount rule by index
     */
    async updateDiscount(id, updatedDiscounts) {
        try {
            const response = await apiFetch({
                path: `${pluginData.restUrl}update-flatpercentage-discount/${id}`,
                method: "POST", // Use "POST" for updates (WP_REST_Server::EDITABLE)
                headers: {
                    "X-WP-Nonce": pluginData.nonce,
                },
                data: updatedDiscounts,
            });

            console.log("Updated Discount Rule:", response);
            return response;
        } catch (error) {
            console.error("Error updating discount:", error);
            throw new Error(error.message);
        }
    },

    async deleteCoupon(id) {
        return apiFetch({
            path: `${pluginData.restUrl}delete-flatpercentage-discount/${id}`,
            method: "DELETE",
            headers: { "X-WP-Nonce": pluginData.nonce },
        });
    }


};
