import { useState } from 'react';
import AppBar from '@mui/material/AppBar';
import Box from '@mui/material/Box';
import Container from '@mui/material/Container';
import Tab from '@mui/material/Tab';
import Tabs from '@mui/material/Tabs';
import Toolbar from '@mui/material/Toolbar';
import Typography from '@mui/material/Typography';
import StraightenIcon from '@mui/icons-material/Straighten';
import SpeedIcon from '@mui/icons-material/Speed';
import ThermostatIcon from '@mui/icons-material/Thermostat';

import ConverterCard from './components/ConverterCard';

const CONVERTISSEURS = [
    {
        label: 'Distance',
        icon: <StraightenIcon />,
        apiUrl: '/api/conversion/distance',
        directions: [
            { value: 'imperial_vers_metrique', fromLabel: 'Pieds (ft)', toLabel: 'Mètres (m)', toUnit: 'm' },
            { value: 'metrique_vers_imperial', fromLabel: 'Mètres (m)', toLabel: 'Pieds (ft)', toUnit: 'ft' },
        ],
    },
    {
        label: 'Vitesse',
        icon: <SpeedIcon />,
        apiUrl: '/api/conversion/vitesse',
        directions: [
            { value: 'kmh_vers_mph', fromLabel: 'km/h', toLabel: 'mph', toUnit: 'mph' },
            { value: 'mph_vers_kmh', fromLabel: 'mph', toLabel: 'km/h', toUnit: 'km/h' },
        ],
    },
    {
        label: 'Température',
        icon: <ThermostatIcon />,
        apiUrl: '/api/conversion/temperature',
        directions: [
            { value: 'celsius_vers_fahrenheit', fromLabel: 'Celsius (°C)', toLabel: 'Fahrenheit (°F)', toUnit: '°F' },
            { value: 'fahrenheit_vers_celsius', fromLabel: 'Fahrenheit (°F)', toLabel: 'Celsius (°C)', toUnit: '°C' },
        ],
    },
];

export default function App() {
    const [onglet, setOnglet] = useState(0);
    const convertisseur = CONVERTISSEURS[onglet];

    return (
        <Box sx={{ minHeight: '100vh', bgcolor: 'background.default' }}>
            <AppBar position="static" elevation={0}>
                <Toolbar>
                    <Typography variant="h6" component="h1" sx={{ color: 'common.white', fontWeight: 700 }}>
                        Convertisseur d'unités
                    </Typography>
                </Toolbar>
            </AppBar>

            <Container maxWidth="sm" sx={{ py: 5 }}>
                <Typography variant="body1" color="text.secondary" sx={{ mb: 3, textAlign: 'center' }}>
                    Convertissez des distances, des vitesses et des températures entre le système impérial et le
                    système métrique.
                </Typography>

                <Tabs
                    value={onglet}
                    onChange={(_, valeur) => setOnglet(valeur)}
                    variant="fullWidth"
                    textColor="inherit"
                    sx={{ mb: 3, bgcolor: 'background.paper', borderRadius: 3, p: 0.5, boxShadow: 1 }}
                    TabIndicatorProps={{ style: { display: 'none' } }}
                >
                    {CONVERTISSEURS.map((item, index) => (
                        <Tab
                            key={item.label}
                            label={item.label}
                            icon={item.icon}
                            iconPosition="start"
                            sx={{
                                borderRadius: 2.5,
                                minHeight: 44,
                                color: index === onglet ? 'primary.contrastText' : 'text.secondary',
                                bgcolor: index === onglet ? 'primary.main' : 'transparent',
                                '& .MuiTab-icon': {
                                    color: index === onglet ? 'primary.contrastText' : 'text.secondary',
                                },
                            }}
                        />
                    ))}
                </Tabs>

                <ConverterCard
                    key={convertisseur.label}
                    title={convertisseur.label}
                    icon={convertisseur.icon}
                    apiUrl={convertisseur.apiUrl}
                    directions={convertisseur.directions}
                />
            </Container>
        </Box>
    );
}
