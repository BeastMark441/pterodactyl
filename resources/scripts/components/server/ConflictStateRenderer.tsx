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
            title={'Выполняется установка'}
            image={ServerInstallSvg}
            message={'Ваш сервер скоро будет готов. Пожалуйста, попробуйте снова через несколько минут.'}
        />
    ) : status === 'suspended' ? (
        <ScreenBlock
            title={'Сервер приостановлен'}
            image={ServerErrorSvg}
            message={'Этот сервер приостановлен и недоступен.'}
        />
    ) : isNodeUnderMaintenance ? (
        <ScreenBlock
            title={'Нода на обслуживании'}
            image={ServerErrorSvg}
            message={'Нода, на которой находится этот сервер, сейчас находится на обслуживании.'}
        />
    ) : (
        <ScreenBlock
            title={isTransferring ? 'Перенос сервера' : 'Восстановление из резервной копии'}
            image={ServerRestoreSvg}
            message={
                isTransferring
                    ? 'Ваш сервер переносится на новую ноду. Пожалуйста, зайдите позже.'
                    : 'Ваш сервер сейчас восстанавливается из резервной копии. Пожалуйста, зайдите снова через несколько минут.'
            }
        />
    );
};