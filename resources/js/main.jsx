import { StrictMode, useMemo } from 'react';
import { createRoot } from 'react-dom/client';
import CssBaseline from '@mui/material/CssBaseline';
import { ThemeProvider } from '@mui/material/styles';

import App from './App';
import { createAppTheme } from './theme';
import useColorMode from './useColorMode';

function Racine() {
    const [mode, basculerMode] = useColorMode();
    const theme = useMemo(() => createAppTheme(mode), [mode]);

    return (
        <ThemeProvider theme={theme}>
            <CssBaseline />
            <App mode={mode} onToggleMode={basculerMode} />
        </ThemeProvider>
    );
}

const container = document.getElementById('app');

if (container) {
    createRoot(container).render(
        <StrictMode>
            <Racine />
        </StrictMode>,
    );
}
