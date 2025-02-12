import { ElNotification } from 'element-plus';
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