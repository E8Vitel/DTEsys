export default function Checkbox({ className = '', ...props }) {
    return (
        <input
            {...props}
            type="checkbox"
            className={
                'rounded border-amber-300 text-amber-500 shadow-sm focus:ring-amber-400 ' +
                className
            }
        />
    );
}
