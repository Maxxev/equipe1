import { useState } from 'react';
import Alert from '@mui/material/Alert';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Divider from '@mui/material/Divider';
import IconButton from '@mui/material/IconButton';
import Stack from '@mui/material/Stack';
import TextField from '@mui/material/TextField';
import Tooltip from '@mui/material/Tooltip';
import Typography from '@mui/material/Typography';
import SwapHorizIcon from '@mui/icons-material/SwapHoriz';

import { postConversion } from '../api';

const NOMBRE_VALIDE = /^-?\d+(\.\d+)?$/;

export default function ConverterCard({ title, icon, apiUrl, directions }) {
    const [directionIndex, setDirectionIndex] = useState(0);
    const [valeur, setValeur] = useState('');
    const [resultat, setResultat] = useState(null);
    const [erreur, setErreur] = useState(null);
    const [enCours, setEnCours] = useState(false);

    const direction = directions[directionIndex];

    const inverserDirection = () => {
        setDirectionIndex((index) => (index + 1) % directions.length);
        setValeur('');
        setResultat(null);
        setErreur(null);
    };

    const echouer = (message) => {
        setErreur(message);
        setResultat(null);
        setValeur('');
    };

    const convertir = async () => {
        const saisie = valeur.trim();

        if (saisie === '') {
            echouer('Veuillez entrer une valeur.');
            return;
        }

        if (!NOMBRE_VALIDE.test(saisie)) {
            echouer('Veuillez entrer un nombre valide (les lettres et symboles ne sont pas acceptés).');
            return;
        }

        setEnCours(true);

        try {
            const data = await postConversion(apiUrl, {
                valeur: Number(saisie),
                direction: direction.value,
            });
            setResultat(data.resultat);
            setErreur(null);
        } catch (e) {
            echouer(e.message);
        } finally {
            setEnCours(false);
        }
    };

    const surAppuiTouche = (event) => {
        if (event.key === 'Enter') {
            convertir();
        }
    };

    return (
        <Card elevation={3}>
            <CardContent>
                <Stack direction="row" spacing={1.5} alignItems="center" sx={{ mb: 2 }}>
                    <Box sx={{ display: 'flex', color: 'primary.main' }}>{icon}</Box>
                    <Typography variant="h6" sx={{ lineHeight: 1 }}>
                        {title}
                    </Typography>
                </Stack>

                <Stack direction="row" spacing={1} alignItems="center" justifyContent="center" sx={{ mb: 2 }}>
                    <Typography variant="body2" color="text.secondary" sx={{ minWidth: 0, lineHeight: 1 }}>
                        {direction.fromLabel}
                    </Typography>
                    <Tooltip title="Inverser le sens de conversion">
                        <IconButton
                            color="primary"
                            size="small"
                            onClick={inverserDirection}
                            aria-label="Inverser le sens de conversion"
                            sx={{ p: 0.5 }}
                        >
                            <SwapHorizIcon fontSize="small" />
                        </IconButton>
                    </Tooltip>
                    <Typography variant="body2" color="text.secondary" sx={{ lineHeight: 1 }}>
                        {direction.toLabel}
                    </Typography>
                </Stack>

                <TextField
                    fullWidth
                    label={`Valeur en ${direction.fromLabel}`}
                    value={valeur}
                    onChange={(event) => setValeur(event.target.value)}
                    onKeyDown={surAppuiTouche}
                    error={Boolean(erreur)}
                    inputProps={{ inputMode: 'decimal' }}
                    sx={{ mb: 1.5 }}
                />

                {erreur && (
                    <Alert severity="error" sx={{ mb: 1.5 }}>
                        {erreur}
                    </Alert>
                )}

                <Button
                    fullWidth
                    variant="contained"
                    onClick={convertir}
                    disabled={enCours}
                >
                    Convertir
                </Button>

                {resultat !== null && (
                    <>
                        <Divider sx={{ my: 2 }} />
                        <Box sx={{ textAlign: 'center' }}>
                            <Typography variant="body2" color="text.secondary">
                                Résultat
                            </Typography>
                            <Typography variant="h4" color="primary.dark">
                                {resultat} {direction.toUnit}
                            </Typography>
                        </Box>
                    </>
                )}
            </CardContent>
        </Card>
    );
}
