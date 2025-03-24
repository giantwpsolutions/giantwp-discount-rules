import { ElNotification } from 'element-plus';
import { DeleteFilled } from '@element-plus/icons-vue'
const { __ } = wp.i18n;

export const discountCreatedMessage = () => {
    ElNotification({
        title: __("Congrats!", "dealbuilder-discount-rules"),
        message: __("Discount Rule Created", "dealbuilder-discount-rules"),
        type: "success",
        offset: 100,
    });
};

export const warningMessage = () => {
    ElNotification({
        title: __("Warning!", "dealbuilder-discount-rules"),
        message: __("Please fill all required fields", "dealbuilder-discount-rules"),
        type: "warning",
        offset: 100,
    });
};

export const errorMessage = () => {
    ElNotification({
        title: __("Error!", "dealbuilder-discount-rules"),
        message: __("Having error saving coupon", "dealbuilder-discount-rules"),
        type: "error",
        offset: 100,
    });
};

export const updatedDiscountMessage = () => {
    ElNotification({
        title: __("Updated!", "dealbuilder-discount-rules"),
        message: __("The discount rule has been updated successfully.", "dealbuilder-discount-rules"),
        type: "success",
        offset: 100,
    });
};

export const deleteMessage = () => {
    ElNotification({
        title: __("Deleted!", "dealbuilder-discount-rules"),
        message: __("The discount rule has been Deleted successfully.", "dealbuilder-discount-rules"),
        icon: DeleteFilled,
        customClass: 'deletebutton',
        offset: 100,
    });
};

export const updatedDiscountStatus = () => {
    ElNotification({
        title: __("Status Updated!", "dealbuilder-discount-rules"),
        message: __("The discount status has been updated successfully.", "dealbuilder-discount-rules"),
        type: "success",
        offset: 100,
    });
};

export const noChanges = () => {
    ElNotification({
        title: __("No Changes!", "dealbuilder-discount-rules"),
        message: __("No changes were made in this rule.", "dealbuilder-discount-rules"),
        type: "warning",
        offset: 100,
    });
};

export const settingsUpdate = () => {
    ElNotification({
        title: __("Updated Settings!", "dealbuilder-discount-rules"),
        message: __("The Settings has been updated successfully.", "dealbuilder-discount-rules"),
        type: "success",
        offset: 100,
    });
};