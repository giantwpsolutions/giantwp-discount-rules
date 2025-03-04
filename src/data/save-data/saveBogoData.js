import apiFetch from "@wordpress/api-fetch";

export const saveBogoData = {
    /**
   * Save a new discount rule
   */
    async saveCoupon(newData) {
        try {
            // Fetch existing data before appending
            const existingData = await apiFetch({
                path: `${pluginData.restUrl}get-bogo-discount`,
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

            // Ensure buy product are formatted properly
            const formattedBuyProduct = Array.isArray(newData.buyProduct)
                ? newData.buyProduct.map((c) => ({
                    field: c.field || "",
                    operator: c.operator || "",
                    value: Array.isArray(c.value) && !c.value.some(Array.isArray) ? c.value : [c.value],

                }))
                : [];

            console.log("Final Conditions Before Sending:", formattedConditions);
            console.log("Final buy Product Before Sending:", formattedBuyProduct);

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
                buyProductCount: newData.buyProductCount || 0,
                getProductCount: newData.getProductCount || 0,
                freeOrDiscount: newData.freeOrDiscount ?? "freeproduct",
                isRepeat: newData.isRepeat || true,
                discounttypeBogo: newData.discounttypeBogo || "fixed",
                discountValue: newData.discountValue || 0,
                maxValue: newData.maxValue || null,
                bogoApplies: newData.bogoApplies ?? "any",
                buyProduct: formattedBuyProduct,
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
                path: `${pluginData.restUrl}save-bogo-discount`,
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
                path: `${pluginData.restUrl}update-bogo-discount/${id}`,
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

    // async updateDiscount(id, updatedFields) {
    //     try {
    //         console.log(`📡 Sending API Request to update discount: ${id}`, updatedFields);

    //         const response = await apiFetch({
    //             path: `${pluginData.restUrl}update-bogo-discount/${id}`,
    //             method: "POST",
    //             headers: {
    //                 "X-WP-Nonce": pluginData.nonce,
    //                 "Content-Type": "application/json",
    //             },
    //             body: JSON.stringify(updatedFields),
    //         });

    //         console.log("✅ Received Response from API:", response);

    //         if (!response?.success) {
    //             throw new Error(response?.message || 'Update failed');
    //         }

    //         return response.data;
    //     } catch (error) {
    //         console.error("❌ Update error:", error);
    //         throw error;
    //     }
    // },

    async deleteCoupon(id) {
        return apiFetch({
            path: `${pluginData.restUrl}delete-bogo-discount/${id}`,
            method: "DELETE",
            headers: { "X-WP-Nonce": pluginData.nonce },
        });
    }


};
