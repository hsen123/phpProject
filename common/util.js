export const isNumber = num =>
    num !== null &&
    num !== undefined &&
    num !== Infinity &&
    !isNaN(num) &&
    !Number.isNaN(num);
