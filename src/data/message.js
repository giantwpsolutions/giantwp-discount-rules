import { ElNotification } from 'element-plus';
import { DeleteFilled } from '@element-plus/icons-vue'
const { __ } = wp.i18n;

export const discountCreatedMessage = () => {
    ElNotification({
        title: __("Congrats!", "giantwp-discount-rules"),
        message: __("Discount Rule Created", "giantwp-discount-rules"),
        type: "success",
        offset: 100,
    });
};

export const warningMessage = () => {
    ElNotification({
        title: __("Warning!", "giantwp-discount-rules"),
        message: __("Please fill all required fields", "giantwp-discount-rules"),
        type: "warning",
        offset: 100,
    });
};

export const errorMessage = () => {
    ElNotification({
        title: __("Error!", "giantwp-discount-rules"),
        message: __("Having error saving coupon", "giantwp-discount-rules"),
        type: "error",
        offset: 100,
    });
};

export const updatedDiscountMessage = () => {
    ElNotification({
        title: __("Updated!", "giantwp-discount-rules"),
        message: __("The discount rule has been updated successfully.", "giantwp-discount-rules"),
        type: "success",
        offset: 100,
    });
};

export const deleteMessage = () => {
    ElNotification({
        title: __("Deleted!", "giantwp-discount-rules"),
        message: __("The discount rule has been Deleted successfully.", "giantwp-discount-rules"),
        icon: DeleteFilled,
        customClass: 'deletebutton',
        offset: 100,
    });
};

export const updatedDiscountStatus = () => {
    ElNotification({
        title: __("Status Updated!", "giantwp-discount-rules"),
        message: __("The discount status has been updated successfully.", "giantwp-discount-rules"),
        type: "success",
        offset: 100,
    });
};

export const noChanges = () => {
    ElNotification({
        title: __("No Changes!", "giantwp-discount-rules"),
        message: __("No changes were made in this rule.", "giantwp-discount-rules"),
        type: "warning",
        offset: 100,
    });
};

export const settingsUpdate = () => {
    ElNotification({
        title: __("Updated Settings!", "giantwp-discount-rules"),
        message: __("The Settings has been updated successfully.", "giantwp-discount-rules"),
        type: "success",
        offset: 100,
    });
};