import apiFetch from "@wordpress/api-fetch";

export const saveBuyXGetYData = {
    /**
   * Save a new discount rule
   */
    async saveCoupon(newData) {
        try {
            // Fetch existing data before appending
            // const existingData = await apiFetch({
            //     path: `${pluginData.restUrl}get-bxgy-discount`,
            //     method: "GET",
            //     headers: {
            //         "X-WP-Nonce": pluginData.nonce,
            //     },
            // });

            // const previousDiscounts = Array.isArray(existingData) ? existingData : [];


            // Ensure conditions are formatted properly
            const formattedConditions = Array.isArray(newData.conditions)
                ? newData.conditions.map((c) => ({
                    field: c.field || "",
                    operator: c.operator || "",
                    value: Array.isArray(c.value) ? c.value : [c.value],
                }))
                : [];

            // Ensure buy product are formatted properly
            const formattedBuyxProduct = Array.isArray(newData.buyProduct)
                ? newData.buyProduct.map((c) => ({
                    buyProductCount: c.buyProductCount || 1,
                    field: c.field || "",
                    operator: c.operator || "",
                    value: Array.isArray(c.value) && !c.value.some(Array.isArray) ? c.value : [c.value],

                }))
                : [];

            // Ensure buy product are formatted properly
            const formattedGetYProduct = Array.isArray(newData.getProduct)
                ? newData.getProduct.map((c) => ({
                    getProductCount: c.field || 1,
                    field: c.field || "",
                    operator: c.operator || "",
                    value: Array.isArray(c.value) && !c.value.some(Array.isArray) ? c.value : [c.value],

                }))
                : [];

            console.log("Final Conditions Before Sending:", formattedConditions);


            const generateUniqueId = () => `dsc-${Date.now().toString(36)}-${Math.random().toString(36).substring(2, 8)}`;

            const validateStatus = (status) =>
                ['on', 'off'].includes(status) ? status : 'on';

            // Prepare new discount entry
            const newDiscount = {
                id: generateUniqueId(),
                createdAt: new Date().toISOString(),
                discountType: newData.discountType,
                status: validateStatus(newData.status),
                couponName: newData.couponName,
                buyXApplies: newData.buyXApplies ?? "any",
                buyProduct: formattedBuyxProduct,
                freeOrDiscount: newData.freeOrDiscount ?? "free_product",
                isRepeat: newData.isRepeat || true,
                discountTypeBxgy: newData.discountTypeBxgy || "fixed",
                discountValue: newData.discountValue || 0,
                maxValue: newData.maxValue || null,
                getYApplies: newData.getYApplies ?? "any",
                getProduct: formattedGetYProduct,
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

            // ✅ Save the updated data
            const response = await apiFetch({
                path: `${pluginData.restUrl}save-bxgy-discount`,
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
    async updateDiscount(id, updatedFields) {
        try {
            console.log("📡 Sending API Request to update discount:", id, updatedFields);

            const payload = {
                ...updatedFields,
                status: ['on', 'off'].includes(updatedFields.status) ? updatedFields.status : 'on'
            };

            const response = await apiFetch({
                path: `${pluginData.restUrl}update-bxgy-discount/${id}`,
                method: "POST",
                headers: {
                    "X-WP-Nonce": pluginData.nonce,
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(payload),
            });

            console.log("✅ Received Response from API:", response);

            if (!response || typeof response !== "object" || !response.success) {
                console.error("❌ API Response Error:", response);
                throw new Error(response?.message || "Unknown API error");
            }

            return response;  // ✅ Fix: Ensure it returns the response

        } catch (error) {
            console.error("❌ Update error:", error);
            throw error;
        }
    },



    async deleteCoupon(id) {
        return apiFetch({
            path: `${pluginData.restUrl}delete-bxgy-discount/${id}`,
            method: "DELETE",
            headers: { "X-WP-Nonce": pluginData.nonce },
        });
    }


};
