import { createTheme } from '@mui/material/styles';

const theme = createTheme({
    palette: {
        mode: 'light',
        primary: {
            main: '#7C4DBF',
            light: '#A578E0',
            dark: '#5A2E96',
            contrastText: '#ffffff',
        },
        secondary: {
            main: '#C77DFF',
        },
        background: {
            default: '#F4EEFB',
            paper: '#FFFFFF',
        },
        error: {
            main: '#B3261E',
        },
        text: {
            primary: '#2E1A47',
            secondary: '#6B5580',
        },
    },
    shape: {
        borderRadius: 16,
    },
    typography: {
        fontFamily: '"Instrument Sans", "Segoe UI", system-ui, sans-serif',
        h4: {
            fontWeight: 700,
        },
        h6: {
            fontWeight: 600,
        },
    },
    components: {
        MuiPaper: {
            styleOverrides: {
                root: {
                    backgroundImage: 'none',
                },
            },
        },
        MuiAppBar: {
            styleOverrides: {
                root: {
                    backgroundImage: 'linear-gradient(135deg, #7C4DBF 0%, #A578E0 100%)',
                },
            },
        },
        MuiButton: {
            styleOverrides: {
                root: {
                    textTransform: 'none',
                    fontWeight: 600,
                    borderRadius: 12,
                },
            },
        },
        MuiTab: {
            styleOverrides: {
                root: {
                    textTransform: 'none',
                    fontWeight: 600,
                },
            },
        },
    },
});

export default theme;
