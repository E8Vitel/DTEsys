export default function InputLabel({
    value,
    className = '',
    children,
    ...props
}) {
    return (
        <label
            {...props}
            className={
                `block text-sm font-bold uppercase tracking-[0.18em] text-amber-500 ` +
                className
            }
        >
            {value ? value : children}
        </label>
    );
}
