import { ElNotification } from 'element-plus';
import { DeleteFilled } from '@element-plus/icons-vue'
const { __ } = wp.i18n;

export const discountCreatedMessage = () => {
    ElNotification({
        title: __("Congrats!", "all-in-one-woodiscount"),
        message: __("Discount Rule Created", "all-in-one-woodiscount"),
        type: "success",
        offset: 100,
    });
};

export const warningMessage = () => {
    ElNotification({
        title: __("Warning!", "all-in-one-woodiscount"),
        message: __("Please fill all required fields", "all-in-one-woodiscount"),
        type: "warning",
        offset: 100,
    });
};

export const errorMessage = () => {
    ElNotification({
        title: __("Error!", "all-in-one-woodiscount"),
        message: __("Having error saving coupon", "all-in-one-woodiscount"),
        type: "error",
        offset: 100,
    });
};

export const updatedDiscountMessage = () => {
    ElNotification({
        title: __("Updated!", "all-in-one-woodiscount"),
        message: __("The discount rule has been updated successfully.", "all-in-one-woodiscount"),
        type: "success",
        offset: 100,
    });
};

export const deleteMessage = () => {
    ElNotification({
        title: __("Deleted!", "all-in-one-woodiscount"),
        message: __("The discount rule has been Deleted successfully.", "all-in-one-woodiscount"),
        icon: DeleteFilled,
        customClass: 'deletebutton',
        offset: 100,
    });
};

export const updatedDiscountStatus = () => {
    ElNotification({
        title: __("Status Updated!", "all-in-one-woodiscount"),
        message: __("The discount status has been updated successfully.", "all-in-one-woodiscount"),
        type: "success",
        offset: 100,
    });
};