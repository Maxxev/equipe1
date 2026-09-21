import { createTheme } from '@mui/material/styles';

const PALETTES = {
    light: {
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
    dark: {
        // "dark" est utilisé comme couleur de texte (résultat) : on le garde lisible sur fond sombre.
        primary: {
            main: '#B790EC',
            light: '#D3B8F7',
            dark: '#D3B8F7',
            contrastText: '#1E0F33',
        },
        secondary: {
            main: '#D9A6FF',
        },
        background: {
            default: '#150E20',
            paper: '#211632',
        },
        error: {
            main: '#F2B8B5',
        },
        text: {
            primary: '#EFE7FA',
            secondary: '#B5A3CC',
        },
    },
};

const APP_BAR_GRADIENTS = {
    light: 'linear-gradient(135deg, #7C4DBF 0%, #A578E0 100%)',
    dark: 'linear-gradient(135deg, #3B1F66 0%, #5A2E96 100%)',
};

export function createAppTheme(mode) {
    return createTheme({
        palette: {
            mode,
            ...PALETTES[mode],
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
                        backgroundImage: APP_BAR_GRADIENTS[mode],
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
}
