export default function InputError({ message, className = '', ...props }) {
    return message ? (
        <p
            {...props}
            className={'text-sm font-bold text-amber-600 ' + className}
        >
            {message}
        </p>
    ) : null;
}
