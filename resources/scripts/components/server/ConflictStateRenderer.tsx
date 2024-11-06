import React from 'react';
import { ServerContext } from '@/state/server';
import ScreenBlock from '@/components/elements/ScreenBlock';
import ServerInstallSvg from '@/assets/images/server_installing.svg';
import ServerErrorSvg from '@/assets/images/server_error.svg';
import ServerRestoreSvg from '@/assets/images/server_restore.svg';

export default () => {
    const status = ServerContext.useStoreState((state) => state.server.data?.status || null);
    const isTransferring = ServerContext.useStoreState((state) => state.server.data?.isTransferring || false);
    const isNodeUnderMaintenance = ServerContext.useStoreState(
        (state) => state.server.data?.isNodeUnderMaintenance || false
    );

    return status === 'installing' || status === 'install_failed' || status === 'reinstall_failed' ? (
        <ScreenBlock
            title={'Выполняется Установка'}
            image={ServerInstallSvg}
            message={'Ваш сервер должен быть готов скоро, пожалуйста, попробуйте снова через несколько минут.'}
        />
    ) : status === 'suspended' ? (
        <ScreenBlock
            title={'Сервер Приостановлен'}
            image={ServerErrorSvg}
            message={'Этот сервер приостановлен и недоступен.'}
        />
    ) : isNodeUnderMaintenance ? (
        <ScreenBlock
            title={'Узел на Техобслуживании'}
            image={ServerErrorSvg}
            message={'Узел этого сервера в настоящее время находится на техническом обслуживании.'}
        />
    ) : (
        <ScreenBlock
            title={isTransferring ? 'Передача' : 'Восстановление из Резервной Копии'}
            image={ServerRestoreSvg}
            message={
                isTransferring
                    ? 'Ваш сервер переносится на новый узел, пожалуйста, проверьте позже.'
                    : 'Ваш сервер в настоящее время восстанавливается из резервной копии, пожалуйста, проверьте через несколько минут.'
            }
        />
    );
};
