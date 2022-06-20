FROM rabbitmq:3.10-alpine

RUN apk add --no-cache \
    curl

RUN curl -L https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases/download/3.10.2/rabbitmq_delayed_message_exchange-3.10.2.ez -o /opt/rabbitmq/plugins/rabbitmq_delayed_message_exchange-3.10.2.ez -s

RUN rabbitmq-plugins enable rabbitmq_delayed_message_exchange
