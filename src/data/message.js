import { ElNotification } from 'element-plus';
import { DeleteFilled } from '@element-plus/icons-vue'
const { __ } = wp.i18n;

export const discountCreatedMessage = () => {
    ElNotification({
        title: __("Congrats!", "aio-woodiscount"),
        message: __("Discount Rule Created", "aio-woodiscount"),
        type: "success",
        offset: 100,
    });
};

export const warningMessage = () => {
    ElNotification({
        title: __("Warning!", "aio-woodiscount"),
        message: __("Please fill all required fields", "aio-woodiscount"),
        type: "warning",
        offset: 100,
    });
};

export const errorMessage = () => {
    ElNotification({
        title: __("Error!", "aio-woodiscount"),
        message: __("Having error saving coupon", "aio-woodiscount"),
        type: "error",
        offset: 100,
    });
};

export const updatedDiscountMessage = () => {
    ElNotification({
        title: __("Updated!", "aio-woodiscount"),
        message: __("The discount rule has been updated successfully.", "aio-woodiscount"),
        type: "success",
        offset: 100,
    });
};

export const deleteMessage = () => {
    ElNotification({
        title: __("Deleted!", "aio-woodiscount"),
        message: __("The discount rule has been Deleted successfully.", "aio-woodiscount"),
        icon: DeleteFilled,
        customClass: 'deletebutton',
        offset: 100,
    });
};

export const updatedDiscountStatus = () => {
    ElNotification({
        title: __("Status Updated!", "aio-woodiscount"),
        message: __("The discount status has been updated successfully.", "aio-woodiscount"),
        type: "success",
        offset: 100,
    });
};